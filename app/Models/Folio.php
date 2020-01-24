<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Folio
 * 
 * @property int $numfol
 * @property int $numres
 * @property int $codeve
 * @property int $tipdoc
 * @property string $cedula
 * @property string $nit
 * @property string $nitage
 * @property int $locpro
 * @property int $codpai
 * @property int $codciu
 * @property int $paides
 * @property int $locdes
 * @property int $ciudes
 * @property int $codtra
 * @property int $trasal
 * @property int $codmot
 * @property string $numhab
 * @property int $usuout
 * @property int $codusu
 * @property Carbon $fecres
 * @property Carbon $feclle
 * @property Carbon $fecsal
 * @property string $hora
 * @property string $horsal
 * @property int $numadu
 * @property int $numnin
 * @property int $numinf
 * @property string $nota
 * @property string $notaayb
 * @property string $equipaje
 * @property string $placa
 * @property string $trahot
 * @property int $estpai
 * @property string $corregir
 * @property int $forpag
 * @property string $estado
 * @property string $walkin
 * @property string $tippro
 * @property string $tipgar
 * @property int $codven
 * @property string $idresweb
 * @property string $idcanal
 * @property string $idclifre
 * @property string $firma
 *
 * @package App\Models
 */
class Folio extends Model
{
	protected $table = 'folio';
	protected $primaryKey = 'numfol';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'numfol' => 'int',
		'numres' => 'int',
		'codeve' => 'int',
		'tipdoc' => 'int',
		'locpro' => 'int',
		'codpai' => 'int',
		'codciu' => 'int',
		'paides' => 'int',
		'locdes' => 'int',
		'ciudes' => 'int',
		'codtra' => 'int',
		'trasal' => 'int',
		'codmot' => 'int',
		'usuout' => 'int',
		'codusu' => 'int',
		'numadu' => 'int',
		'numnin' => 'int',
		'numinf' => 'int',
		'estpai' => 'int',
		'forpag' => 'int',
		'codven' => 'int'
	];

	protected $dates = [
		'fecres',
		'feclle',
		'fecsal'
	];

	protected $fillable = [
		'numres',
		'codeve',
		'tipdoc',
		'cedula',
		'nit',
		'nitage',
		'locpro',
		'codpai',
		'codciu',
		'paides',
		'locdes',
		'ciudes',
		'codtra',
		'trasal',
		'codmot',
		'numhab',
		'usuout',
		'codusu',
		'fecres',
		'feclle',
		'fecsal',
		'hora',
		'horsal',
		'numadu',
		'numnin',
		'numinf',
		'nota',
		'notaayb',
		'equipaje',
		'placa',
		'trahot',
		'estpai',
		'corregir',
		'forpag',
		'estado',
		'walkin',
		'tippro',
		'tipgar',
		'codven',
		'idresweb',
		'idcanal',
		'idclifre',
		'firma'
	];
}
