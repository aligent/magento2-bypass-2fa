<?php
/*
 * @author Aligent Consulting Team
 * @copyright Copyright (c) 2021 Aligent Consulting. (http://www.aligent.com.au)
 */

namespace Aligent\Bypass2FA\Plugin;

use Aligent\Bypass2FA\Helper\Data;
use Magento\TwoFactorAuth\Model\TfaSession;

class Bypass2FAAdmin
{
    /**
     * @var Data
     */
    protected Data $data;

    /**
     * BypassTwoFactorAuth constructor.
     * @param Data $data
     */
    public function __construct(
        Data $data
    ) {
        $this->data = $data;
    }

    /**
     * Enables the bypass of 2FA for admin access.
     *
     * If 2FA bypass is enabled, return true so all requests bypass 2FA.
     * Otherwise, return the original result
     *
     * @param TfaSession $subject
     * @param $result
     * @return bool
     */
    public function afterIsGranted(
        TfaSession $subject,
        $result
    ): bool {
        return $this->data->getBypassAdmin() ? true : $result;
    }
}
