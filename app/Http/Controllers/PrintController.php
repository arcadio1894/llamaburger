<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\UserCoupon;
use Illuminate\Http\Request;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\ImagickEscposImage; // Para manejar imágenes
use Barryvdh\DomPDF\Facade\Pdf;

class PrintController extends Controller
{
    public function printByMilly( Request $request )
    {

    }

    public function show($id)
    {
        // Buscar la boleta por ID
        $boleta = Boleta::find($id);

        if (!$boleta) {
            // Si no existe, retornar un mensaje de error
            return response()->json(['error' => 'Boleta no encontrada'], 404);
        }

        // Retornar la boleta en formato JSON
        return response()->json($boleta);
    }

    public function imprimir($id)
    {
        //$boleta = Boleta::find($id);

        /*if (!$boleta) {
            return response()->json(['error' => 'Boleta no encontrada'], 404);
        }*/

        try {
            // Conectar a la impresora
            $connector = new WindowsPrintConnector("EPSON TM-T20III Receipt");
            $printer = new Printer($connector);

            // Cargar e imprimir el logotipo
            $logoPath = public_path('images/logo/logoPequeño.png'); // Ruta del logotipo
            if (file_exists($logoPath)) {
                $logo = ImagickEscposImage::load($logoPath);
                $printer->graphics($logo); // Imprime la imagen
            }

            // Encabezado de la boleta
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("RESTAURANTE PIZZA HOUSE\n");
            $printer->text("RUC: 12345678901\n");
            $printer->text("Av. Principal 123, Lima\n");
            $printer->text("--------------------------------\n");
            $printer->text("BOLETA DE VENTA\n");
            /*$printer->text("Nro: {$boleta->numero_boleta}\n");
            $printer->text("Cliente: {$boleta->cliente}\n");
            $printer->text("--------------------------------\n");

            // Detalle de productos
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            foreach ($boleta->productos as $producto) {
                $nombre = str_pad($producto['nombre'], 20);
                $precio = str_pad('S/. ' . number_format($producto['precio'] * $producto['cantidad'], 2), 10, ' ', STR_PAD_LEFT);
                $printer->text("{$nombre}{$precio}\n");
                $printer->text("  {$producto['cantidad']} x S/. {$producto['precio']}\n");
                if ($producto['descuento'] > 0) {
                    $printer->text("  Descuento: S/. {$producto['descuento']}\n");
                }
            }

            $printer->text("--------------------------------\n");

            // Totales
            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("Subtotal: S/. {$boleta->subtotal}\n");
            $printer->text("Descuento: S/. {$boleta->descuento}\n");
            $printer->text("IGV: S/. {$boleta->igv}\n");
            $printer->text("TOTAL: S/. {$boleta->total}\n");*/
            $printer->text("--------------------------------\n");

            // Pie de página
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Gracias por su compra\n");
            $printer->text("www.pizzahouse.com\n");

            // Finalizar impresión
            $printer->cut();
            $printer->close();

            return response()->json(['success' => 'Boleta impresa correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al imprimir: ' . $e->getMessage()], 500);
        }
    }

    public function printOrder($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Boleta no encontrada'
            ], 420);
        }

        try {
            // Conectar a la impresora
            dump("Inicio del codigo");
            $connector = new WindowsPrintConnector("EPSON TM-T20III Receipt");
            dump("Conector listo");
            $printer = new Printer($connector);
            dump("Printer listo");
            // Cargar e imprimir el logotipo
            $logoPath = public_path('images/logo/logoPequeño.png'); // Ruta del logotipo
            if (file_exists($logoPath)) {
                $logo = ImagickEscposImage::load($logoPath);
                $printer->graphics($logo); // Imprime la imagen
            }
            dump("Imagen listo");

            // Encabezado de la boleta
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("RESTAURANTE FUEGO Y MASA\n");
            $printer->text("RUC: 12345678901\n");
            $printer->text("Av. Principal 123, Lima\n");
            $printer->text("--------------------------------\n");
            $printer->text("BOLETA DE VENTA\n");
            $printer->text("Nro: ORDEN - {$order->id}\n");
            $printer->text("Cliente: {$order->user->name}\n");
            $printer->text("--------------------------------\n");
            dump("Encabezado listo");

            // Detalle de productos
            $printer->setJustification(Printer::JUSTIFY_LEFT);

            foreach ($order->details as $detail) {

                $nombre = str_pad($detail->product->full_name."|".$detail->productType->type->name."(".$detail->productType->type->size.")", 50);
                $precio = str_pad('S/. ' . number_format($detail->price * $detail->quantity, 2), 10, ' ', STR_PAD_LEFT);
                $printer->text("{$nombre}{$precio}\n");
                $printer->text("  {$detail->quantity} x S/. {$detail->price}\n");
                /*if ($producto['descuento'] > 0) {
                    $printer->text("  Descuento: S/. {$producto['descuento']}\n");
                }*/
            }

            dump("Detalles listo");

            $printer->text("--------------------------------\n");

            $userCoupon = UserCoupon::where('order_id', $order->id)->first();

            if ($userCoupon) {
                // Si existe un descuento, restar el discount_amount del total
                $discount =  number_format($userCoupon->discount_amount, 2, '.', '');
            } else {
                $discount =  number_format(0, 2, '.', '');

            }

            // Totales

            $amount_total = round($order->total_amount + $order->amount_shipping, 2);
            $amount_subtotal = number_format(round($amount_total/1.18, 2), 2, '.', '');
            $amount_igv = round($amount_total - $amount_subtotal, 2);

            $printer->setJustification(Printer::JUSTIFY_RIGHT);
            $printer->text("Subtotal: S/. {$amount_subtotal}\n");
            $printer->text("Descuento: S/. {$discount}\n");
            $printer->text("IGV: S/. {$amount_igv}\n");
            $printer->text("TOTAL: S/. {$order->amount_pay}\n");
            $printer->text("--------------------------------\n");
            dump("Totales listo");

            // Pie de página
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Gracias por su compra\n");
            $printer->text("www.fuegoymasa.com\n");

            dump("Pie de pagina listo");
            // Finalizar impresión
            $printer->cut();
            dump("Cut listo");
            $printer->close();
            dump("Close listo");

            return response()->json([
                'success' => true,
                'message' => 'Comanda impresa correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al imprimir: ' . $e->getMessage()], 500);
        }
    }

    public function generarRecibo($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Boleta no encontrada'
            ], 420);
        }

        $userCoupon = UserCoupon::where('order_id', $order->id)->first();

        if ($userCoupon) {
            // Si existe un descuento, restar el discount_amount del total
            $discount =  number_format($userCoupon->discount_amount, 2, '.', '');
        } else {
            $discount =  number_format(0, 2, '.', '');

        }
        // Totales

        $amount_total = round($order->total_amount + $order->amount_shipping, 2);
        $amount_shipping = round($order->amount_shipping, 2);
        $amount_only_total = round($order->total_amount, 2);
        $amount_subtotal = number_format(round($amount_only_total/1.18, 2), 2, '.', '');
        $amount_igv = round($amount_only_total - $amount_subtotal, 2);

        $totals = $order->data_totals;

        $pdf = Pdf::loadView('order.recibo', compact('order','amount_total', 'amount_subtotal', 'amount_igv', 'discount', 'amount_shipping', 'amount_only_total', 'totals'))
            ->setPaper([0, 0, 226.8, 900], 'portrait'); // 80mm de ancho, altura dinámica

        return $pdf->stream("recibo_{$order->id}.pdf");
    }

    public function generarComanda($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'error' => true,
                'message' => 'Boleta no encontrada'
            ], 420);
        }

        $userCoupon = UserCoupon::where('order_id', $order->id)->first();

        if ($userCoupon) {
            // Si existe un descuento, restar el discount_amount del total
            $discount =  number_format($userCoupon->discount_amount, 2, '.', '');
        } else {
            $discount =  number_format(0, 2, '.', '');

        }
        // Totales

        $amount_total = round($order->total_amount + $order->amount_shipping, 2);
        $amount_subtotal = number_format(round($amount_total/1.18, 2), 2, '.', '');
        $amount_igv = round($amount_total - $amount_subtotal, 2);

        $totals = $order->data_totals;

        $pdf = Pdf::loadView('order.comanda', compact('order','amount_total', 'amount_subtotal', 'amount_igv', 'discount', 'totals'))
            ->setPaper([0, 0, 226.8, 900], 'portrait'); // 80mm de ancho, altura dinámica

        return $pdf->stream("recibo_{$order->id}.pdf");
    }
}
