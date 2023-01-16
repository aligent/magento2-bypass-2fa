<?php
/*
 * @author Aligent Consulting Team
 * @copyright Copyright (c) 2022 Aligent Consulting. (http://www.aligent.com.au)
 */
declare(strict_types=1);

namespace Aligent\Bypass2FA\Helper;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;

class Data
{
    public const XML_PATH_ALLOWED_BYPASS_HOSTNAMES = 'bypass_2fa/settings/allowed_hostnames';
    public const XML_PATH_ALLOWED_BYPASS_USERNAMES = 'bypass_2fa/settings/allowed_usernames';
    public const ENV_VARIABLE_BYPASS_2FA_ADMIN = 'BYPASS_2FA_ADMIN';
    public const ENV_VARIABLE_BYPASS_2FA_API = 'BYPASS_2FA_API';

    /**
     * @var DeploymentConfig
     */
    private DeploymentConfig $deploymentConfig;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfigInterface;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * Constructor
     *
     * @param DeploymentConfig $deploymentConfig
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param UrlInterface $url
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        ScopeConfigInterface $scopeConfigInterface,
        UrlInterface $url
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->url = $url;
    }

    /**
     * Indicates if 2FA can be bypassed for admin access
     *
     * @return bool
     */
    public function getBypassAdmin(): bool
    {
        return $this->isBypassAllowed(self::ENV_VARIABLE_BYPASS_2FA_ADMIN);
    }

    /**
     * Indicates if 2FA can be bypassed for API token generation
     *
     * @return bool
     */
    public function getBypassAPI(): bool
    {
        return $this->isBypassAllowed(self::ENV_VARIABLE_BYPASS_2FA_API);
    }

    /**
     * Validate the usernames that can be bypassed
     *
     * @param string $userName
     * @return bool
     */
    public function isAllowedBypassAPIByUsername(string $userName): bool
    {
        $allowedUsernames = $this->getAllowedBypassUsernames();
        return in_array($userName, $allowedUsernames);
    }

    /**
     * Returns array of admin usernames that can be bypass
     *
     * @return String[]
     */
    private function getAllowedBypassUsernames(): array
    {
        $allowedUsernames = $this->scopeConfigInterface->getValue(self::XML_PATH_ALLOWED_BYPASS_USERNAMES);
        if (empty($allowedUsernames)) {
            $allowedUsernames = [];
        }
        return $allowedUsernames;
    }

    /**
     * Returns array of (partial) hostnames that can be bypassed
     *
     * @return String[]
     */
    private function getAllowedBypassHostnames(): array
    {
        $allowedHostnames = $this->scopeConfigInterface->getValue(self::XML_PATH_ALLOWED_BYPASS_HOSTNAMES);
        if (empty($allowedHostnames)) {
            return [];
        }
        return $allowedHostnames;
    }

    /**
     * Get Environment Variable
     *
     * @param string $environmentVariable
     * @return bool
     */
    private function getEnvironmentVariable(string $environmentVariable): bool
    {
        if (array_key_exists($environmentVariable, $_ENV)) { // phpcs:ignore
            return $_ENV[$environmentVariable] == 1; // phpcs:ignore
        }
        return false;
    }

    /**
     * Checks if 2FA authentication can be bypassed based on environment and hostname
     *
     * @param string $environmentVariable
     * @return bool
     */
    private function isBypassAllowed(string $environmentVariable): bool
    {
        try {
            // check if bypass mode is enabled for the environment
            // check both deploymentConfig (ie env.php) and environment variables (for Magento Cloud)
            if ($this->deploymentConfig->get($environmentVariable) !== true &&
                $this->getEnvironmentVariable($environmentVariable) !== true) {
                return false;
            }
            return $this->validateCurrentHostname();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate current hostname with allowed list
     *
     * @return bool
     */
    private function validateCurrentHostname(): bool
    {
        // check that current base url is configured as an allowed hostname
        $baseUrl = $this->url->getBaseUrl();
        foreach ($this->getAllowedBypassHostnames() as $hostname) {
            if (strpos($baseUrl, $hostname) !== false) {
                return true;
            }
        }
        return false;
    }
}
