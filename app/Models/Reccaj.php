<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Reccaj
 * 
 * @property int $numrec
 * @property string $cedula
 * @property string $nombre
 * @property string $direccion
 * @property string $ciudad
 * @property string $telefono
 * @property Carbon $fecha
 * @property int $codcaj
 * @property int $codusu
 * @property int $codcar
 * @property int $codven
 * @property string $nota
 * @property string $estado
 *
 * @package App\Models
 */
class Reccaj extends Model
{
	protected $table = 'reccaj';
	protected $primaryKey = 'numrec';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'numrec' => 'int',
		'codcaj' => 'int',
		'codusu' => 'int',
		'codcar' => 'int',
		'codven' => 'int'
	];

	protected $dates = [
		'fecha'
	];

	protected $fillable = [
		'cedula',
		'nombre',
		'direccion',
		'ciudad',
		'telefono',
		'fecha',
		'codcaj',
		'codusu',
		'codcar',
		'codven',
		'nota',
		'estado'
	];
}
