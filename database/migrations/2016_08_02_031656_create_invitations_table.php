<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvitationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('groom_name');
            $table->string('bride_name');
            $table->dateTime('wedding_date');
            $table->string('phone');
            $table->text('images');
            $table->string('hotel_address');
            $table->string('hotel_name');
            $table->string('hotel_room');
            $table->string('hotel_phone');
            $table->string('music');
            $table->string('template_name');
            $table->dateTime('last_publish_time')->nullable();
            $table->enum('state', ['NotInit','Init','WaitPublish','Published','Frozen']);
            $table->integer('user_id');
            $table->index('user_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('invitations');
    }
}
