<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groupId = DB::table('groups')->where('code', 'ADMIN')->value('id');

        if (! $groupId) {
            $groupId = DB::table('groups')->insertGetId([
                'code' => 'ADMIN',
                'description' => 'Administradores',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! DB::table('users')->where('email', 'admin@admin.com')->exists()) {
            DB::table('users')->insert([
                'name' => 'Administrador',
                'email' => 'admin@admin.com',
                'password' => bcrypt('admin'),
                'group_id' => $groupId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'admin@admin.com')->delete();
    }
};
