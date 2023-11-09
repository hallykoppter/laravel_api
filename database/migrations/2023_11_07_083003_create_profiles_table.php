<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('nama_lengkap', 100)->nullable(false);
            $table->string('kelas', 100)->nullable(false);
            $table->string('tempat_lahir', 100)->nullable(false);
            $table->date('tanggal_lahir')->nullable(false);
            $table->string('alamat', 100)->nullable();
            $table->integer('jenis_kelamin')->nullable(false);
            $table->string('foto', 100)->nullable();
            $table->unsignedBigInteger('user_id')->nullable(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
