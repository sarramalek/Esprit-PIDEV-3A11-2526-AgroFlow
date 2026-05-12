<?php
// src/EventListener/TwoFactorListener.php
namespace App\EventListener;

// Ce listener est désactivé — la logique 2FA est gérée dans
// App\Security\LoginFormAuthenticator::onAuthenticationSuccess()