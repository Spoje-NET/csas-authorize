<?php

declare(strict_types=1);

/**
 * This file is part of the CSASAuthorize package
 *
 * https://github.com/Spoje-NET/csas-authorize
 *
 * (c) Spoje.Net IT s.r.o. <https://spojenet.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SpojeNet\CSas;

/**
 * CSAS Developer Portal Data Importer
 * 
 * This class helps import application data from CSAS Developer Portal
 * to avoid manual data entry in CSAS Authorize.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class DeveloperPortalImporter
{
    private Application $application;

    public function __construct()
    {
        $this->application = new Application();
    }

    /**
     * Import application data from JSON export file.
     * 
     * Expected JSON structure should contain application details
     * as they appear in CSAS Developer Portal.
     *
     * @param string $jsonFilePath Path to JSON export file
     * @return bool Success status
     * @throws \RuntimeException When file cannot be read or parsed
     */
    public function importFromJson(string $jsonFilePath): bool
    {
        if (!file_exists($jsonFilePath)) {
            throw new \RuntimeException(_('Import file not found: ') . $jsonFilePath);
        }

        $jsonContent = file_get_contents($jsonFilePath);
        if ($jsonContent === false) {
            throw new \RuntimeException(_('Cannot read import file: ') . $jsonFilePath);
        }

        $data = json_decode($jsonContent, true);
        if ($data === null) {
            throw new \RuntimeException(_('Invalid JSON format in file: ') . $jsonFilePath);
        }

        return $this->processApplicationData($data);
    }

    /**
     * Import application data from array (e.g., from API response).
     *
     * @param array<string, mixed> $applicationData Application data array
     * @return bool Success status
     */
    public function importFromArray(array $applicationData): bool
    {
        return $this->processApplicationData($applicationData);
    }

    /**
     * Process and validate application data, then save to database.
     *
     * @param array<string, mixed> $data Raw application data
     * @return bool Success status
     */
    private function processApplicationData(array $data): bool
    {
        // Map CSAS Developer Portal fields to CSAS Authorize database fields
        $mappedData = $this->mapPortalFields($data);
        
        // Validate required fields
        if (!$this->validateRequiredFields($mappedData)) {
            return false;
        }

        // Set the data and save
        $this->application->takeData($mappedData);
        
        if ($this->application->saveToSQL()) {
            $this->application->addStatusMessage(
                sprintf(_('Application "%s" imported successfully'), $mappedData['name']),
                'success'
            );
            return true;
        } else {
            $this->application->addStatusMessage(
                _('Failed to save imported application data'),
                'error'
            );
            return false;
        }
    }

    /**
     * Map CSAS Developer Portal field names to CSAS Authorize field names.
     *
     * @param array<string, mixed> $portalData Data from Developer Portal
     * @return array<string, mixed> Mapped data for CSAS Authorize
     */
    private function mapPortalFields(array $portalData): array
    {
        $mapped = [];

        // Basic application information
        $mapped['name'] = $portalData['name'] ?? $portalData['applicationName'] ?? '';
        $mapped['uuid'] = $portalData['id'] ?? $portalData['applicationId'] ?? $portalData['uuid'] ?? '';
        $mapped['logo'] = $portalData['logoUrl'] ?? $portalData['logo'] ?? '';
        $mapped['email'] = $portalData['email'] ?? $portalData['contactEmail'] ?? '';

        // Sandbox environment credentials
        if (isset($portalData['sandbox'])) {
            $sandbox = $portalData['sandbox'];
            $mapped['sandbox_client_id'] = $sandbox['clientId'] ?? $sandbox['client_id'] ?? '';
            $mapped['sandbox_client_secret'] = $sandbox['clientSecret'] ?? $sandbox['client_secret'] ?? '';
            $mapped['sandbox_api_key'] = $sandbox['apiKey'] ?? $sandbox['api_key'] ?? '';
            $mapped['sandbox_redirect_uri'] = $sandbox['redirectUri'] ?? $sandbox['redirect_uri'] ?? '';
        }

        // Production environment credentials
        if (isset($portalData['production'])) {
            $production = $portalData['production'];
            $mapped['production_client_id'] = $production['clientId'] ?? $production['client_id'] ?? '';
            $mapped['production_client_secret'] = $production['clientSecret'] ?? $production['client_secret'] ?? '';
            $mapped['production_api_key'] = $production['apiKey'] ?? $production['api_key'] ?? '';
            $mapped['production_redirect_uri'] = $production['redirectUri'] ?? $production['redirect_uri'] ?? '';
        }

        // Alternative flat structure mapping
        if (!isset($portalData['sandbox']) && !isset($portalData['production'])) {
            // Try direct field mapping for flat structure
            $mapped['sandbox_client_id'] = $portalData['sandboxClientId'] ?? $portalData['sandbox_client_id'] ?? '';
            $mapped['sandbox_client_secret'] = $portalData['sandboxClientSecret'] ?? $portalData['sandbox_client_secret'] ?? '';
            $mapped['sandbox_api_key'] = $portalData['sandboxApiKey'] ?? $portalData['sandbox_api_key'] ?? '';
            $mapped['sandbox_redirect_uri'] = $portalData['sandboxRedirectUri'] ?? $portalData['sandbox_redirect_uri'] ?? '';
            
            $mapped['production_client_id'] = $portalData['productionClientId'] ?? $portalData['production_client_id'] ?? '';
            $mapped['production_client_secret'] = $portalData['productionClientSecret'] ?? $portalData['production_client_secret'] ?? '';
            $mapped['production_api_key'] = $portalData['productionApiKey'] ?? $portalData['production_api_key'] ?? '';
            $mapped['production_redirect_uri'] = $portalData['productionRedirectUri'] ?? $portalData['production_redirect_uri'] ?? '';
        }

        // Remove empty values to avoid overwriting existing data
        return array_filter($mapped, function($value) {
            return !empty($value);
        });
    }

    /**
     * Validate that required fields are present in mapped data.
     *
     * @param array<string, mixed> $data Mapped application data
     * @return bool Validation result
     */
    private function validateRequiredFields(array $data): bool
    {
        $requiredFields = ['name', 'uuid'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $this->application->addStatusMessage(
                    sprintf(_('Required field "%s" is missing or empty'), $field),
                    'error'
                );
                return false;
            }
        }

        // At least one environment should have credentials
        $hasSandbox = !empty($data['sandbox_client_id']) && !empty($data['sandbox_client_secret']);
        $hasProduction = !empty($data['production_client_id']) && !empty($data['production_client_secret']);

        if (!$hasSandbox && !$hasProduction) {
            $this->application->addStatusMessage(
                _('At least one environment (sandbox or production) must have complete credentials'),
                'error'
            );
            return false;
        }

        return true;
    }

    /**
     * Generate example JSON structure for manual export from Developer Portal.
     *
     * @return string JSON example
     */
    public static function getJsonExample(): string
    {
        $example = [
            'name' => 'My Application Name',
            'id' => 'application-uuid-from-portal',
            'logoUrl' => 'https://example.com/logo.png',
            'email' => 'developer@example.com',
            'sandbox' => [
                'clientId' => 'sandbox-client-uuid',
                'clientSecret' => 'sandbox-client-secret',
                'apiKey' => 'sandbox-api-key-uuid',
                'redirectUri' => 'https://myapp.example.com/sandbox/callback'
            ],
            'production' => [
                'clientId' => 'production-client-uuid',
                'clientSecret' => 'production-client-secret',
                'apiKey' => 'production-api-key-uuid',
                'redirectUri' => 'https://myapp.example.com/production/callback'
            ]
        ];

        return json_encode($example, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get the imported application instance.
     *
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }
}
