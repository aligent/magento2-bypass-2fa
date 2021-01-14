<?php
/*
 * @author Aligent Consulting Team
 * @copyright Copyright (c) 2021 Aligent Consulting. (http://www.aligent.com.au)
 */

namespace Aligent\Bypass2FA\Plugin;

use Closure;
use Aligent\Bypass2FA\Helper\Data;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Integration\Api\AdminTokenServiceInterface;
use Magento\TwoFactorAuth\Model\AdminAccessTokenService;

class Bypass2FAWebApi
{
    /**
     * @var Data
     */
    protected Data $data;
    /**
     * @var AdminTokenServiceInterface
     */
    protected AdminTokenServiceInterface $adminTokenService;

    public function __construct(
        Data $data,
        AdminTokenServiceInterface $adminTokenService
    ) {
        $this->data = $data;
        $this->adminTokenService = $adminTokenService;
    }

    /**
     * Enables the bypass of 2FA for API token generation.
     *
     * @param AdminAccessTokenService $subject
     * @param Closure $proceed
     * @param string $username
     * @param string $password
     * @return string
     * @throws AuthenticationException
     * @throws InputException
     * @throws LocalizedException
     */
    public function aroundCreateAdminAccessToken(
        AdminAccessTokenService $subject,
        Closure $proceed,
        $username,
        $password
    ): string {
        return $this->data->getBypassAPI() ?
            $this->adminTokenService->createAdminAccessToken($username, $password) :
            $proceed($username, $password);
    }
}
