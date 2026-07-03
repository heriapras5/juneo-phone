<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Services table indexes
        Schema::table('services', function (Blueprint $table) {
            if (!$this->indexExists('services', 'services_tanggal_index')) {
                $table->index('tanggal');
            }
            if (!$this->indexExists('services', 'services_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('services', 'services_tanggal_status_index')) {
                $table->index(['tanggal', 'status']); // Composite for dashboard queries
            }
            if (!$this->indexExists('services', 'services_customer_id_index')) {
                $table->index('customer_id');
            }
        });

        // Service_items table indexes
        if (Schema::hasTable('service_items')) {
            Schema::table('service_items', function (Blueprint $table) {
                if (!$this->indexExists('service_items', 'service_items_service_id_index')) {
                    $table->index('service_id');
                }
            });
        }

        // Service_histories table indexes
        if (Schema::hasTable('service_histories')) {
            Schema::table('service_histories', function (Blueprint $table) {
                if (!$this->indexExists('service_histories', 'service_histories_service_id_index')) {
                    $table->index('service_id');
                }
                if (!$this->indexExists('service_histories', 'service_histories_service_id_created_at_index')) {
                    $table->index(['service_id', 'created_at']); // For history queries
                }
            });
        }

        // Biaya_harian table indexes
        if (Schema::hasTable('biaya_harian')) {
            Schema::table('biaya_harian', function (Blueprint $table) {
                if (!$this->indexExists('biaya_harian', 'biaya_harian_tanggal_kategori_index')) {
                    $table->index(['tanggal', 'kategori']); // Composite for frequent queries
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if ($this->indexExists('services', 'services_tanggal_index')) {
                $table->dropIndex(['tanggal']);
            }
            if ($this->indexExists('services', 'services_status_index')) {
                $table->dropIndex(['status']);
            }
            if ($this->indexExists('services', 'services_tanggal_status_index')) {
                $table->dropIndex(['tanggal', 'status']);
            }
            if ($this->indexExists('services', 'services_customer_id_index')) {
                $table->dropIndex(['customer_id']);
            }
        });

        if (Schema::hasTable('service_items')) {
            Schema::table('service_items', function (Blueprint $table) {
                if ($this->indexExists('service_items', 'service_items_service_id_index')) {
                    $table->dropIndex(['service_id']);
                }
            });
        }

        if (Schema::hasTable('service_histories')) {
            Schema::table('service_histories', function (Blueprint $table) {
                if ($this->indexExists('service_histories', 'service_histories_service_id_index')) {
                    $table->dropIndex(['service_id']);
                }
                if ($this->indexExists('service_histories', 'service_histories_service_id_created_at_index')) {
                    $table->dropIndex(['service_id', 'created_at']);
                }
            });
        }

        if (Schema::hasTable('biaya_harian')) {
            Schema::table('biaya_harian', function (Blueprint $table) {
                if ($this->indexExists('biaya_harian', 'biaya_harian_tanggal_kategori_index')) {
                    $table->dropIndex(['tanggal', 'kategori']);
                }
            });
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        
        $result = DB::select(
            "SELECT COUNT(*) as count 
             FROM information_schema.statistics 
             WHERE table_schema = ? 
             AND table_name = ? 
             AND index_name = ?",
            [$database, $table, $index]
        );
        
        return $result[0]->count > 0;
    }
};
