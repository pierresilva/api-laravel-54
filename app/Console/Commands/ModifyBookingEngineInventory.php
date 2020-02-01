<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ModifyBookingEngineInventory extends Command
{
    private $bookingEngine;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cr:put_inventory
                            {start-date : Fecha inicial}
                            {end-date : Fecha final}
                            {room-class : Id de la clase de habitación}
                            {booking-engine : Código del motor de reservas.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $class = 'App\\' . studly_case($this->argument('booking-engine')) . '\\' . studly_case($this->argument('booking-engine'));
        $this->bookingEngine = new $class();
        //

        $typeRoom = config( snake_case(studly_case($this->argument('booking-engine'))) . '.rooms_lc.' .$this->argument('room-class'));

        if ($typeRoom) {
            $period = new \DatePeriod(
                new \DateTime($this->argument('start-date')),
                new \DateInterval('P1D'),
                new \DateTime($this->argument('end-date'))
            );
            $datesToCheck = [];

            foreach ($period as $key => $value) {
                $datesToCheck[] = $value->format('Y-m-d');
            }

            foreach ($datesToCheck as $dateToCheck) {
                $availability = $this->bookingEngine->getBambooQuantityAvailability($dateToCheck, $dateToCheck, $this->argument('room-class'));
                $availability['date'] = $dateToCheck;
                $availabilities[] = $availability;
            }

            $modifies = [];
            foreach ($availabilities as $availability) {
                $modifies[] = $this->bookingEngine->modifyInventory($availability['date'], $availability['date'], $availability['class'], null, $availability['rooms']);
            }

            return;
        }

        return;
    }
}
