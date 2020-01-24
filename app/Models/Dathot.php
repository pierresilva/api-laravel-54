<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Dathot
 * 
 * @property string $nit
 * @property string $tipest
 * @property string $nombre
 * @property string $nomcad
 * @property string $nomger
 * @property int $codpai
 * @property string $nomdep
 * @property string $nomciu
 * @property string $direccion
 * @property string $telefono
 * @property string $telefono2
 * @property string $fax
 * @property string $apapos
 * @property string $sitweb
 * @property string $email
 * @property string $resfac
 * @property Carbon $fecfac
 * @property string $prefac
 * @property int $numfac
 * @property int $numfai
 * @property int $numfaf
 * @property string $notfac
 * @property string $notrec
 * @property string $notreg
 * @property string $notica
 * @property string $notsoft
 * @property int $numpre
 * @property int $numrec
 * @property int $numegr
 * @property int $numcam
 * @property int $condas
 * @property string $coddas
 * @property int $ciudas
 * @property Carbon $fecha
 * @property string $location
 * @property float $longitude
 * @property float $latitude
 * @property string $apikey
 * @property string $whemet
 * @property string $wheater
 * @property string $serial
 * @property string $version
 * @property string $webservice_seven
 * @property string $seven_sucursal
 *
 * @package App\Models
 */
class Dathot extends Model
{
	protected $table = 'dathot';
	protected $primaryKey = 'nit';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'codpai' => 'int',
		'numfac' => 'int',
		'numfai' => 'int',
		'numfaf' => 'int',
		'numpre' => 'int',
		'numrec' => 'int',
		'numegr' => 'int',
		'numcam' => 'int',
		'condas' => 'int',
		'ciudas' => 'int',
		'longitude' => 'float',
		'latitude' => 'float'
	];

	protected $dates = [
		'fecfac',
		'fecha'
	];

	protected $fillable = [
		'tipest',
		'nombre',
		'nomcad',
		'nomger',
		'codpai',
		'nomdep',
		'nomciu',
		'direccion',
		'telefono',
		'telefono2',
		'fax',
		'apapos',
		'sitweb',
		'email',
		'resfac',
		'fecfac',
		'prefac',
		'numfac',
		'numfai',
		'numfaf',
		'notfac',
		'notrec',
		'notreg',
		'notica',
		'notsoft',
		'numpre',
		'numrec',
		'numegr',
		'numcam',
		'condas',
		'coddas',
		'ciudas',
		'fecha',
		'location',
		'longitude',
		'latitude',
		'apikey',
		'whemet',
		'wheater',
		'serial',
		'version',
		'webservice_seven',
		'seven_sucursal'
	];
}
