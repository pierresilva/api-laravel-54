<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Garre
 * 
 * @property int $numres
 * @property int $item
 * @property int $codusu
 * @property int $codcaj
 * @property Carbon $fecha
 * @property int $codcar
 * @property float $total
 * @property int $numrec
 * @property int $numegr
 * @property string $estado
 *
 * @package App\Models
 */
class Garre extends Model
{
	protected $table = 'garres';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'numres' => 'int',
		'item' => 'int',
		'codusu' => 'int',
		'codcaj' => 'int',
		'codcar' => 'int',
		'total' => 'float',
		'numrec' => 'int',
		'numegr' => 'int'
	];

	protected $dates = [
		'fecha'
	];

	protected $fillable = [
		'codusu',
		'codcaj',
		'fecha',
		'codcar',
		'total',
		'numrec',
		'numegr',
		'estado'
	];
}
