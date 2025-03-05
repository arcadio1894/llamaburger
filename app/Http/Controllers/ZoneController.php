<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ZoneController extends Controller
{
    public function index()
    {
        $shops = Shop::all();
        return view('zone.index', compact('shops'));
    }

    public function create()
    {
        $shops = Shop::all();
        return view('zone.create', compact('shops'));
    }

    public function edit(Zone $zone)
    {
        $shops = Shop::all();
        return view('zone.edit', compact('zone', 'shops'));
    }

    public function update(Request $request, Zone $zone)
    {
        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'name' => 'required|string|max:255',
            'coordinates' => 'required|json',
        ]);

        $zone->update($request->all());

        return redirect()->route('zone.index')->with('success', 'Zona actualizada correctamente.');
    }

    public function destroy($id)
    {
        try {
            $zone = Zone::findOrFail($id);
            $zone->delete();

            return response()->json(['success' => true, 'message' => 'Zona eliminada correctamente.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // Obtener las zonas de una tienda
    public function getZones($shopId)
    {
        $zones = Zone::where('shop_id', $shopId)->get()->map(function ($zone) {
            return [
                'id' => $zone->id,
                'name' => $zone->name,
                'status' => $zone->status,
                'price' => $zone->price,
                'coordinates' => $this->convertPolygonToArray($zone->coordinates), // Convertir POLYGON a array
            ];
        });

        return response()->json($zones);
    }

    /**
     * Convierte un objeto GEOMETRY (POLYGON) a un array de coordenadas.
     */
    private function convertPolygonToArray($polygon)
    {
        $coordinates = [];

        if ($polygon) {
            $wkt = DB::selectOne("SELECT ST_AsText(?) AS wkt", [$polygon])->wkt;
            preg_match('/\(\((.*?)\)\)/', $wkt, $matches);

            if (!empty($matches[1])) {
                $points = explode(',', $matches[1]);
                foreach ($points as $point) {
                    list($lng, $lat) = explode(' ', trim($point));
                    $coordinates[] = [floatval($lng), floatval($lat)];
                }
            }
        }

        return $coordinates;
    }

    // Guardar nuevas zonas
    public function store(Request $request)
    {
        $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'zones' => 'required|array',
            'zones.*.coordinates' => 'required|array|min:3',
        ]);

        DB::beginTransaction();
        try {
            $shopId = $request->shop_id;
            $shop = Shop::findOrFail($request->shop_id);
            $baseName = $shop->name; // Nombre base de la tienda

            // Obtener todas las zonas existentes con el mismo nombre base y extraer los números
            $existingZones = Zone::where('shop_id', $shop->id)
                ->where('name', 'LIKE', "$baseName%")
                ->pluck('name')
                ->map(function ($name) use ($baseName) {
                    return (int) str_replace($baseName . ' ', '', $name);
                })
                ->filter()
                ->sort()
                ->values();

            // Buscar el primer número disponible
            $newNumber = 1;
            foreach ($existingZones as $number) {
                if ($number != $newNumber) {
                    break;
                }
                $newNumber++;
            }

            // Generar el nuevo nombre con el primer número disponible
            $zoneName = $baseName . ' ' . $newNumber;

            // 🔍 Obtener las zonas actuales de la tienda con sus coordenadas
            $existingZones = Zone::where('shop_id', $shopId)->get()->keyBy('id');

            // 🔄 Lista de IDs de zonas que se mantendrán
            $zonesToKeep = [];

            foreach ($request->zones as $zoneData) {
                $coordinates = $zoneData['coordinates'];

                // 🔄 Convertir coordenadas a formato WKT POLYGON
                $wktPolygon = "POLYGON((";
                foreach ($coordinates as $point) {
                    $wktPolygon .= number_format($point[0], 6, '.', '') . " " . number_format($point[1], 6, '.', '') . ",";
                }
                $wktPolygon = rtrim($wktPolygon, ',') . "))";

                // 🚀 Buscar si existe una zona con las mismas coordenadas
                $matchedZone = null;
                foreach ($existingZones as $zone) {
                    $dbCoordinates = DB::selectOne("SELECT ST_AsText(coordinates) as coords FROM zones WHERE id = ?", [$zone->id]);

                    if ($dbCoordinates && trim($dbCoordinates->coords) === trim($wktPolygon)) {
                        $matchedZone = $zone;
                        break;
                    }
                }

                if ($matchedZone) {
                    // ✅ La zona ya existe con las mismas coordenadas, la mantenemos
                    $zonesToKeep[] = $matchedZone->id;
                } else {
                    // 🚀 Verificar si la zona existe con coordenadas diferentes (actualización)
                    $updated = false;
                    foreach ($existingZones as $zone) {
                        if (!in_array($zone->id, $zonesToKeep)) {
                            // 🔄 Si encontramos una zona sin usar, la actualizamos con las nuevas coordenadas
                            $zone->update([
                                'coordinates' => DB::raw("ST_PolygonFromText('$wktPolygon')")
                            ]);
                            $zonesToKeep[] = $zone->id;
                            $updated = true;
                            break;
                        }
                    }

                    // 🆕 Si no se pudo actualizar, creamos una nueva zona
                    if (!$updated) {
                        $newZone = Zone::create([
                            'shop_id' => $shopId,
                            'name' => $zoneName,
                            'coordinates' => DB::raw("ST_PolygonFromText('$wktPolygon')"),
                        ]);
                        $zonesToKeep[] = $newZone->id;
                    }
                }
            }

            // ❌ Eliminar solo las zonas que ya no están en la lista enviada
            Zone::where('shop_id', $shopId)
                ->whereNotIn('id', $zonesToKeep)
                ->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Zonas actualizadas correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function changeStatus($id)
    {
        $zone = Zone::findOrFail($id);
        $zone->status = ($zone->status == 'active') ? 'inactive' : 'active'; // Cambiar de activo a inactivo o viceversa
        $zone->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado correctamente.',
            'coordinates' => $this->convertPolygonToArray($zone->coordinates),
            'status' => $zone->status,
        ]);
    }

    public function deleteZone($id)
    {
        $zone = Zone::findOrFail($id);
        $zone->delete();

        return response()->json(['success' => true, 'message' => 'Zona eliminada correctamente.']);
    }

    public function updatePrice(Request $request, Zone $zone)
    {
        $request->validate([
            'price' => 'required|numeric|min:0'
        ]);

        $zone->update(['price' => $request->price]);

        return response()->json(['success' => true]);
    }

    public function show(Zone $zone)
    {
        return response()->json([
            'id' => $zone->id,
            'name' => $zone->name,
            'price' => $zone->price,
            'coordinates' => $this->convertPolygonToArray($zone->coordinates), // Convierte el POLYGON en array
            'shop_latitude' => $zone->shop->latitude, // Latitud de la tienda
            'shop_longitude' => $zone->shop->longitude // Longitud de la tienda
        ]);
    }
}
