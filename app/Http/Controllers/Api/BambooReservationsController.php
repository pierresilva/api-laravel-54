<?php

namespace App\Http\Controllers\Api;

use App\Database;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BambooReservationsController extends Controller
{
    //

    public function storeReservation(Request $request)
    {
        $requestData = $request->all();
        $database = Database::where('code', $requestData['database'])->first();

        if (!$database) {
            return response()->json([
                'message' => 'No se encontro la base de datos: ' . $request->database . ' en el host: ' . $request->host,
            ], 404);
        }

        $reservation = $requestData['@attributes'];

        $customer = [
            'cedula' => '',
        ];

        $reserva = DB::insert("
        insert into reserva
        (numres ,referencia ,tipdoc ,cedula ,numhab, tipres, codusu, fecres, feclle, fecsal, feclim, numadu, numnin, numinf, observacion, numpre, carta, habfij, solicitada, forpag, fecest, estado, tippro, tipgar, codven)
        values
        ('$numres','$referencia',{$tdoc},'$Documento','$numero_hab','5','1',curdate(),'$fechauno','$fechados' ,'$fechauno','$numadu','$numnin','$numinf','$observa','$numpre','N','N','$solicita','$forpag',curdate(),'P','1','3','1');
        ");

        $cliente = DB::insert("
            INSERT INTO clientes
            (cedula, tipdoc, lugexp, categoria, accion, nombre, sexo, telefono1, telefono2, email, direccion, locdir, codpai, codciu, fecnac, locnac, codnac, codpro, ultest, numest, feccre, tipcli, credito, tipcre, cupo, diaven, exento, cuepla, clides, actint, tipinf, observacion, soundphone, estado, estsis, ciudades_dian, cat_caja, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido, tipo_regimen_dian, emailfe, regimen_fiscal, tip_per_jur, codigo_postal)
            VALUES
            ('{$reservation['id']}', {$tdoc}, '', 'D', '', '{$nombre}', 'M', '', '', '{$correo}', '', 0, 169, 1, '1970-01-01', 135, 169, 1, curdate(), 0, curdate(), 1, 'N', 'P', 0, 0, 'N', 'N', 'N', 'S', 'N', '{$observa}', '', 'N', 'A', 0, '', '', '', '', '', '0', '', 0, 0, '');
        ",
            [
                1,
                'Dayle'
            ]);

        return response()->json([
            'message' => 'OK',
            'data' => $users,
        ]);

    }
}
