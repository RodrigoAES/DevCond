<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Unit;
use App\Models\Area;
use App\Models\Wall;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Unit::create([
            'name' => 'AP 100',
            'user_id' => 2, 
        ]);
        Unit::create([
            'name' => 'AP 101',
            'user_id' => 2, 
        ]);
        Unit::create([
            'name' => 'AP 200',
            'user_id' => null, 
        ]);
        Unit::create([
            'name' => 'AP 201',
            'user_id' => null, 
        ]);

        Area::create([
            'allowed' => true,
            'title' => 'Academia',
            'cover' => 'gym.png',
            'days' => '1,2,4,5',
            'start_time' => '06:00:00',
            'end_time' => '22:00:00'
        ]);
        Area::create([
            'allowed' => true,
            'title' => 'piscina',
            'cover' => 'pool.png',
            'days' => '1,2,3,4,5',
            'start_time' => '07:00:00',
            'end_time' => '23:00:00'
        ]);
        Area::create([
            'allowed' => true,
            'title' => 'Churrasqueira',
            'cover' => 'barbecue.png',
            'days' => '4,5,6',
            'start_time' => '09:00:00',
            'end_time' => '23:00:00'
        ]);

        Wall::create([
            'title' => 'Aviso de teste',
            'body' => 'bla bla bla',
            'created_at' => '2020-12-20 15:00:00',
        ]);
        Wall::create([
            'title' => 'Aviso de teste2',
            'body' => 'bla bla bla',
            'created_at' => '2021-11-12 16:00:00',
        ]);
        Wall::create([
            'title' => 'Aviso de teste3',
            'body' => 'bla bla bla',
            'created_at' => '2022-06-20 08:30:00',
        ]);
        
    }
}
