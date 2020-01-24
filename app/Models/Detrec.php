<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Detrec
 * 
 * @property int $numrec
 * @property int $numero
 * @property int $forpag
 * @property int $numfor
 * @property Carbon $fecven
 * @property float $ivarep
 * @property float $valorm
 * @property float $valor
 *
 * @package App\Models
 */
class Detrec extends Model
{
	protected $table = 'detrec';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'numrec' => 'int',
		'numero' => 'int',
		'forpag' => 'int',
		'numfor' => 'int',
		'ivarep' => 'float',
		'valorm' => 'float',
		'valor' => 'float'
	];

	protected $dates = [
		'fecven'
	];

	protected $fillable = [
		'forpag',
		'numfor',
		'fecven',
		'ivarep',
		'valorm',
		'valor'
	];
}
