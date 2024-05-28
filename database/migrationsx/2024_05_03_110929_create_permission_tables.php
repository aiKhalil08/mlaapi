<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // $tableNames = config('permission.table_names');

        // // table names
        // $permissions_table_name = 'permissions';
        // $roles_table_name = 'roles';
        // $permission_user_table_name = 'permission_user';
        // $role_user_table_name = 'role_user';

        // $columnNames = config('permission.column_names');
        // $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        // $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';


        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id'); // permission id
            $table->string('name', 125);       // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name', 125); // For MySQL 8.0 use string('guard_name', 125);
            // $table->timestamps();

            $table->unique('name');
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id'); // role id
            $table->string('name', 125);       // For MySQL 8.0 use string('name', 125);
            $table->string('guard_name', 125); // For MySQL 8.0 use string('guard_name', 125);
            // $table->timestamps();

            $table->unique('name');
        });

        Schema::create('permission_user', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('user_id');
            // $table->string('model_type');
            // $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_permissions_model_id_model_type_index');

            $table->foreign('permission_id')
                ->references('id') // permission id
                ->on('permissions')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id') // permission id
                ->on('users')
                ->onDelete('cascade');

            $table->primary(['permission_id', 'user_id'],
                    'model_has_permissions_permission_model_type_primary');

        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            // $table->string('model_type');
            $table->unsignedBigInteger('user_id');
            // $table->index([$columnNames['model_morph_key'], 'model_type'], 'model_has_roles_model_id_model_type_index');

            $table->foreign('role_id')
                ->references('id') // role id
                ->on('roles')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id') // role id
                ->on('users')
                ->onDelete('cascade');

            $table->primary(['role_id', 'user_id'],
                    'model_has_roles_role_model_type_primary');
            // if ($teams) {
            //     $table->unsignedBigInteger($columnNames['team_foreign_key']);
            //     $table->index($columnNames['team_foreign_key'], 'model_has_roles_team_foreign_key_index');

            //     $table->primary([$columnNames['team_foreign_key'], $pivotRole, $columnNames['model_morph_key'], 'model_type'],
            //         'model_has_roles_role_model_type_primary');
            // } else {
            // }
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->foreign('permission_id')
                ->references('id') // permission id
                ->on('permissions')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id') // role id
                ->on('roles')
                ->onDelete('cascade');

            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        // app('cache')
            // ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            // ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new \Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
        }

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
