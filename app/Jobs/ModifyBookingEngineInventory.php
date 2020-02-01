<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ModifyBookingEngineInventory implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $bookingEngine;
    private $bookingEngineCode;
    private $startDate;
    private $endDate;
    private $roomClass;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($startDate, $endDate, $roomClass, $bookingEngineCode)
    {
        //
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->roomClass = $roomClass;
        $this->bookingEngineCode = $bookingEngineCode;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        $class = 'App\\' . studly_case($this->bookingEngineCode) . '\\' . studly_case($this->bookingEngineCode);
        $this->bookingEngine = new $class();
        //
        $typeRoom = config( snake_case(studly_case($this->bookingEngineCode)) . '.rooms_lc.' . $this->roomClass);

        if ($typeRoom) {
            $period = new \DatePeriod(
                new \DateTime($this->startDate),
                new \DateInterval('P1D'),
                new \DateTime($this->endDate)
            );
            $datesToCheck = [];

            foreach ($period as $key => $value) {
                $datesToCheck[] = $value->format('Y-m-d');
            }

            foreach ($datesToCheck as $dateToCheck) {
                $availability = $this->bookingEngine->getBambooQuantityAvailability($dateToCheck, $dateToCheck, $this->roomClass);
                $availability['date'] = $dateToCheck;
                $availabilities[] = $availability;
            }

            $modifies = [];
            foreach ($availabilities as $availability) {
                $modifies[] = $this->bookingEngine->modifyInventory($availability['date'], $availability['date'], $availability['class'], null, $availability['rooms']);
            }
        }
    }
}
