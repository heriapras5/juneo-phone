<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function indexExists($table, $indexName): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        $result = DB::select(
            "SELECT COUNT(*) as count FROM information_schema.statistics 
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$database, $table, $indexName]
        );
        
        return $result[0]->count > 0;
    }

    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!$this->indexExists('services', 'services_service_number_index')) {
                $table->index('service_number');
            }
            if (!$this->indexExists('services', 'services_tanggal_index')) {
                $table->index('tanggal');
            }
            if (!$this->indexExists('services', 'services_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('services', 'services_customer_id_index')) {
                $table->index('customer_id');
            }
            if (!$this->indexExists('services', 'services_teknisi_index')) {
                $table->index('teknisi');
            }
            if (!$this->indexExists('services', 'services_status_tanggal_index')) {
                $table->index(['status', 'tanggal']);
            }
        });

        if (Schema::hasTable('service_items')) {
            Schema::table('service_items', function (Blueprint $table) {
                if (!$this->indexExists('service_items', 'service_items_service_id_index')) {
                    $table->index('service_id');
                }
            });
        }

        Schema::table('service_histories', function (Blueprint $table) {
            if (!$this->indexExists('service_histories', 'service_histories_service_id_index')) {
                $table->index('service_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if ($this->indexExists('services', 'services_service_number_index')) {
                $table->dropIndex(['service_number']);
            }
            if ($this->indexExists('services', 'services_tanggal_index')) {
                $table->dropIndex(['tanggal']);
            }
            if ($this->indexExists('services', 'services_status_index')) {
                $table->dropIndex(['status']);
            }
            if ($this->indexExists('services', 'services_customer_id_index')) {
                $table->dropIndex(['customer_id']);
            }
            if ($this->indexExists('services', 'services_teknisi_index')) {
                $table->dropIndex(['teknisi']);
            }
            if ($this->indexExists('services', 'services_status_tanggal_index')) {
                $table->dropIndex(['status', 'tanggal']);
            }
        });

        if (Schema::hasTable('service_items')) {
            Schema::table('service_items', function (Blueprint $table) {
                if ($this->indexExists('service_items', 'service_items_service_id_index')) {
                    $table->dropIndex(['service_id']);
                }
            });
        }

        Schema::table('service_histories', function (Blueprint $table) {
            if ($this->indexExists('service_histories', 'service_histories_service_id_index')) {
                $table->dropIndex(['service_id']);
            }
        });
    }
};
