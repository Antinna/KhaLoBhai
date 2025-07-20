<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Database;

class InstallController extends Controller
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * GET /install
     * Automatically run migrations if not already run
     */
    public function index()
    {
        try {
            // Check if we can connect to the database
            if (!$this->checkDatabaseConnection()) {
                return $this->showInstallPage([
                    'status' => 'error',
                    'message' => 'Database connection failed. Please check your database configuration in .env file.',
                    'steps' => []
                ]);
            }

            $installationSteps = [];
            $allSuccess = true;

            // Step 1: Check if migrations table exists
            $installationSteps[] = [
                'step' => 'Checking migrations table',
                'status' => 'running',
                'message' => 'Verifying migrations table exists...'
            ];

            if (!$this->migrationsTableExists()) {
                // Create migrations table
                $result = $this->createMigrationsTable();
                if ($result['success']) {
                    $installationSteps[count($installationSteps) - 1] = [
                        'step' => 'Checking migrations table',
                        'status' => 'success',
                        'message' => 'Migrations table created successfully'
                    ];
                } else {
                    $installationSteps[count($installationSteps) - 1] = [
                        'step' => 'Checking migrations table',
                        'status' => 'error',
                        'message' => 'Failed to create migrations table: ' . $result['error']
                    ];
                    $allSuccess = false;
                }
            } else {
                $installationSteps[count($installationSteps) - 1] = [
                    'step' => 'Checking migrations table',
                    'status' => 'success',
                    'message' => 'Migrations table already exists'
                ];
            }

            // Step 2: Check and run migrations
            if ($allSuccess) {
                $migrationResults = $this->runMigrationsIfNeeded();
                $installationSteps = array_merge($installationSteps, $migrationResults['steps']);
                if (!$migrationResults['success']) {
                    $allSuccess = false;
                }
            }

            // Step 3: Verify tables were created
            if ($allSuccess) {
                $verificationResult = $this->verifyTablesExist();
                $installationSteps[] = $verificationResult;
                if ($verificationResult['status'] !== 'success') {
                    $allSuccess = false;
                }
            }

            // Final status
            $finalStatus = $allSuccess ? 'success' : 'error';
            $finalMessage = $allSuccess 
                ? 'Installation completed successfully! Your API is ready to use.' 
                : 'Installation completed with some errors. Please check the details above.';

            return $this->showInstallPage([
                'status' => $finalStatus,
                'message' => $finalMessage,
                'steps' => $installationSteps
            ]);

        } catch (\Exception $e) {
            return $this->showInstallPage([
                'status' => 'error',
                'message' => 'Installation failed: ' . $e->getMessage(),
                'steps' => []
            ]);
        }
    }

    /**
     * Check if database connection is working
     */
    private function checkDatabaseConnection(): bool
    {
        try {
            $this->db->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if migrations table exists
     */
    private function migrationsTableExists(): bool
    {
        try {
            $query = $this->db->query("SHOW TABLES LIKE 'migrations'");
            return $query->getNumRows() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create migrations table
     */
    private function createMigrationsTable(): array
    {
        try {
            $forge = \Config\Database::forge();
            
            $forge->addField([
                'id' => [
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'version' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'class' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'group' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'namespace' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'time' => [
                    'type' => 'INT',
                    'constraint' => 11,
                ],
                'batch' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
            ]);

            $forge->addKey('id', true);
            $forge->createTable('migrations');

            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Run migrations if they haven't been run yet
     */
    private function runMigrationsIfNeeded(): array
    {
        $steps = [];
        $success = true;

        try {
            // Get list of migration files
            $migrationFiles = $this->getMigrationFiles();
            $pendingMigrations = [];
            
            // Check which migrations need to be run
            foreach ($migrationFiles as $file) {
                $migrationName = pathinfo($file, PATHINFO_FILENAME);
                
                if (!$this->migrationHasBeenRun($migrationName)) {
                    $pendingMigrations[] = $migrationName;
                } else {
                    $steps[] = [
                        'step' => "Checking migration: $migrationName",
                        'status' => 'skipped',
                        'message' => "Migration $migrationName already run, skipping"
                    ];
                }
            }

            // Run all pending migrations at once
            if (!empty($pendingMigrations)) {
                $steps[] = [
                    'step' => 'Running pending migrations',
                    'status' => 'running',
                    'message' => 'Executing ' . count($pendingMigrations) . ' pending migration(s)...'
                ];

                $result = $this->runAllPendingMigrations();
                
                if ($result['success']) {
                    $steps[count($steps) - 1] = [
                        'step' => 'Running pending migrations',
                        'status' => 'success',
                        'message' => 'All pending migrations completed successfully: ' . implode(', ', $pendingMigrations)
                    ];
                } else {
                    $steps[count($steps) - 1] = [
                        'step' => 'Running pending migrations',
                        'status' => 'error',
                        'message' => 'Migration failed: ' . $result['error']
                    ];
                    $success = false;
                }
            } else {
                $steps[] = [
                    'step' => 'Checking migrations',
                    'status' => 'success',
                    'message' => 'All migrations are up to date'
                ];
            }

            if (empty($migrationFiles)) {
                $steps[] = [
                    'step' => 'Checking migrations',
                    'status' => 'info',
                    'message' => 'No migration files found'
                ];
            }

        } catch (\Exception $e) {
            $steps[] = [
                'step' => 'Running migrations',
                'status' => 'error',
                'message' => 'Failed to run migrations: ' . $e->getMessage()
            ];
            $success = false;
        }

        return ['success' => $success, 'steps' => $steps];
    }

    /**
     * Get list of migration files
     */
    private function getMigrationFiles(): array
    {
        $migrationPath = APPPATH . 'Database/Migrations/';
        $files = [];

        if (is_dir($migrationPath)) {
            $files = glob($migrationPath . '*.php');
            sort($files); // Sort to run in order
        }

        return $files;
    }

    /**
     * Check if a migration has been run
     */
    private function migrationHasBeenRun(string $migrationName): bool
    {
        try {
            $query = $this->db->query("SELECT COUNT(*) as count FROM migrations WHERE class LIKE ?", ["%$migrationName%"]);
            $result = $query->getRow();
            return $result->count > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Run all pending migrations
     */
    private function runAllPendingMigrations(): array
    {
        try {
            // Use CodeIgniter's migration runner
            $migrate = \Config\Services::migrations();
            $migrate->setNamespace(null); // Use default namespace
            
            // Run migration to latest version (this will run all pending migrations)
            $result = $migrate->latest();
            
            if ($result !== false) {
                return ['success' => true];
            } else {
                return ['success' => false, 'error' => 'Migration runner returned false'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify that required tables exist
     */
    private function verifyTablesExist(): array
    {
        $requiredTables = ['users', 'posts'];
        $existingTables = [];
        $missingTables = [];

        try {
            foreach ($requiredTables as $table) {
                $query = $this->db->query("SHOW TABLES LIKE '$table'");
                if ($query->getNumRows() > 0) {
                    $existingTables[] = $table;
                } else {
                    $missingTables[] = $table;
                }
            }

            if (empty($missingTables)) {
                return [
                    'step' => 'Verifying database tables',
                    'status' => 'success',
                    'message' => 'All required tables exist: ' . implode(', ', $existingTables)
                ];
            } else {
                return [
                    'step' => 'Verifying database tables',
                    'status' => 'error',
                    'message' => 'Missing tables: ' . implode(', ', $missingTables)
                ];
            }
        } catch (\Exception $e) {
            return [
                'step' => 'Verifying database tables',
                'status' => 'error',
                'message' => 'Failed to verify tables: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Show installation page with results
     */
    private function showInstallPage(array $data)
    {
        return view('install/index', $data);
    }
}