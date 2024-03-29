<?php
/*
 * @author Aligent Consulting Team
 * @copyright Copyright (c) 2022 Aligent Consulting. (http://www.aligent.com.au)
 */
declare(strict_types=1);

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
    private Data $data;

    /**
     * @var AdminTokenServiceInterface
     */
    private AdminTokenServiceInterface $adminTokenService;

    /**
     * Constructor
     *
     * @param Data $data
     * @param AdminTokenServiceInterface $adminTokenService
     */
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
        return $this->data->isAllowedBypassAPIByUsername($username) || $this->data->getBypassAPI() ?
            $this->adminTokenService->createAdminAccessToken($username, $password) :
            $proceed($username, $password);
    }
}
