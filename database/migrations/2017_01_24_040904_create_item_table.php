<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemTable extends Migration
{
    public static $tableName = 'plh_item';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(CreateItemTable::$tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';

            // general info
            $table->increments('id');
            $table->string('title');
            $table->string('desc')->comment('description');
            $table->tinyInteger('category')->comment('0 - 6');
            $table->double('price');
            $table->tinyInteger('condition');
            $table->tinyInteger('status')->comment('0:bid, 1:auction, 2:closed');

            // image
            $table->string('image0')->nullable();
            $table->string('image1')->nullable();
            $table->string('image2')->nullable();
            $table->string('image3')->nullable();

            // time
            $table->dateTime('end_at');
            $table->timestamps();
            $table->softDeletes();

            $table->unsignedInteger('user_id');

            // foreign key
            $table->foreign('user_id')->references('id')
                ->on(CreateUserTable::$tableName)
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(CreateItemTable::$tableName);
    }
}
