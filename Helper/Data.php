<?php
/*
 * @author Aligent Consulting Team
 * @copyright Copyright (c) 2021 Aligent Consulting. (http://www.aligent.com.au)
 */

namespace Aligent\Bypass2FA\Helper;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;

class Data
{
    public const XML_PATH_ALLOWED_BYPASS_HOSTNAMES = 'bypass_2fa/settings/allowed_hostnames';
    public const ENV_VARIABLE_BYPASS_2FA_ADMIN = 'BYPASS_2FA_ADMIN';
    public const ENV_VARIABLE_BYPASS_2FA_API = 'BYPASS_2FA_API';

    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigInterface;
    /**
     * @var UrlInterface
     */
    protected $url;

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
     * @return bool Indicates if 2FA can be bypassed for admin access
     */
    public function getBypassAdmin(): bool
    {
        return $this->isBypassAllowed(self::ENV_VARIABLE_BYPASS_2FA_ADMIN);
    }

    /**
     * @return bool Indicates if 2FA can be bypassed for API token generation
     */
    public function getBypassAPI(): bool
    {
        return $this->isBypassAllowed(self::ENV_VARIABLE_BYPASS_2FA_API);
    }

    /**
     * Returns array of (partial) hostnames that can be bypassed
     * @return String[]
     */
    protected function getAllowedBypassHostnames(): array
    {
        $allowedHostnames = $this->scopeConfigInterface->getValue(self::XML_PATH_ALLOWED_BYPASS_HOSTNAMES);
        if (empty($allowedHostnames)) {
            return [];
        }
        return $allowedHostnames;
    }

    /**
     * Checks if 2FA authentication can be bypassed based on environment and hostname
     * @param string $environmentVariable
     * @return bool
     */
    protected function isBypassAllowed(string $environmentVariable): bool
    {
        try {
            // check if bypass mode is enabled for the environment
            if ($this->deploymentConfig->get($environmentVariable) !== true) {
                return false;
            }
            return $this->validateCurrentHostname();
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function validateCurrentHostname(): bool
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