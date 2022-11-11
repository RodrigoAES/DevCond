<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Models\User;
use App\models\Unit;
use App\Models\Area;
Use App\Models\Wall;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('cpf')->unique();
            $table->string('password');
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignIdFor(User::class)->nullable()->default(null);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('SET NULL');
        });
        Schema::create('unit_residents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Unit::class);
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('CASCADE');
            $table->foreignIdfor(User::class);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->string('name');
            $table->date('birthdate');
        });
        Schema::create('unit_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Unit::class);
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('CASCADE');
            $table->string('title');
            $table->string('color');
            $table->string('plate');
        });
        Schema::create('unit_pets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Unit::class);
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('CASCADE');
            $table->string('name');
            $table->string('breed');
        });

        Schema::create('walls', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('body');
            $table->datetime('created_at');
        });
        Schema::create('wall_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Wall::class);
            $table->foreign('wall_id')->references('id')->on('walls')->onDelete('CASCADE');
            $table->foreignIdFor(User::class);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
        });

        Schema::create('docs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_url');
        });

        Schema::create('billets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Unit::class);
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('CASCADE');
            $table->string('title');
            $table->string('file_url');
        });

        Schema::create('warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Unit::class);
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('CASCADE');
            $table->string('title');
            $table->string('status')->default('IN_REVIEW'); // IN_REVIEW, SOLVED
            $table->date('created_at');
            $table->text('photos')->nullable();
        });

        Schema::create('lost_and_founds', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class);
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('status'); // LOST, FOUNDED, RECOVERED
            $table->string('photo')->nullable();
            $table->string('description');
            $table->string('where');
            $table->date('created_at');
        });

        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->boolean('allowed')->default(true);
            $table->string('title');
            $table->string('cover');
            $table->string('days');
            $table->time('start_time');
            $table->time('end_time');
        });
        Schema::create('area_disabled_days', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Area::class);
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('CASCADE');
            $table->date('day');
        });
        Schema::create('area_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Unit::class);
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('CASCADE');
            $table->foreignIdFor(Area::class);
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('CASCADE');
            $table->datetime('reservation_datetime');
            $table->datetime('reservation_endtime'); //max: 3 Hours (to more time make 1 more reserve max reserves:2); friday, saturday and sunday to make one more reserve stand 3 hours
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('units');
        Schema::dropIfExists('unit_residents');
        Schema::dropIfExists('unit_vehicles');
        Schema::dropIfExists('unit_pets');
        Schema::dropIfExists('walls');
        Schema::dropIfExists('wall_likes');
        Schema::dropIfExists('docs');
        Schema::dropIfExists('billets');
        Schema::dropIfExists('warnigs');
        Schema::dropIfExists('lost_and_founds');
        Schema::dropIfExists('areas');
        Schema::dropIfExists('area_disable_days');
        Schema::dropIfExists('area_reservations');
    }
};
