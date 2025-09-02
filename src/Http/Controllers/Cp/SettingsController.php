<?php

namespace Shopper\Http\Controllers\Cp;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Shopper\CP\Navigation;
use Shopper\CP\Page;
use Shopper\Http\Controllers\Controller;
use Shopper\Repositories\SettingRepository;
use Shopper\Repositories\PaymentGatewayRepository;
use Shopper\Repositories\TaxRateRepository;
use Shopper\Repositories\ShippingMethodRepository;

class SettingsController extends Controller
{
    protected SettingRepository $settingRepository;
    protected PaymentGatewayRepository $paymentGatewayRepository;
    protected TaxRateRepository $taxRateRepository;
    protected ShippingMethodRepository $shippingMethodRepository;

    public function __construct(
        SettingRepository $settingRepository,
        PaymentGatewayRepository $paymentGatewayRepository,
        TaxRateRepository $taxRateRepository,
        ShippingMethodRepository $shippingMethodRepository
    ) {
        $this->settingRepository = $settingRepository;
        $this->paymentGatewayRepository = $paymentGatewayRepository;
        $this->taxRateRepository = $taxRateRepository;
        $this->shippingMethodRepository = $shippingMethodRepository;
    }

    /**
     * Settings overview page
     */
    public function index(): Response
    {
        $page = Page::make(__('settings.title'))
            ->breadcrumb('Home', '/cp')
            ->breadcrumb(__('settings.title'));

        // Get statistics for dashboard
        $stats = $this->getSettingsStats();

        return Inertia::render('settings-index', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'stats' => $stats,
        ]);
    }

    /**
     * Get settings statistics
     */
    private function getSettingsStats(): array
    {
        $paymentGateways = $this->paymentGatewayRepository->all();
        $taxRates = $this->taxRateRepository->all(); 
        $shippingMethods = $this->shippingMethodRepository->all();
        
        $activeGateways = $paymentGateways->filter(fn($gateway) => $gateway->is_enabled)->count();
        $totalGateways = $paymentGateways->count();
        
        $activeTaxRates = $taxRates->filter(fn($rate) => $rate->is_active)->count();
        
        $activeShippingMethods = $shippingMethods->filter(fn($method) => $method->is_enabled)->count();
        
        // Check email configuration
        $emailConfigured = $this->isEmailConfigured();

        return [
            'payment_gateways' => [
                'active' => $activeGateways,
                'total' => $totalGateways,
                'status' => $activeGateways > 0 ? 'success' : 'warning'
            ],
            'tax_rates' => [
                'active' => $activeTaxRates,
                'status' => $activeTaxRates > 0 ? 'success' : 'warning'
            ],
            'shipping_methods' => [
                'active' => $activeShippingMethods,
                'status' => $activeShippingMethods > 0 ? 'success' : 'warning'
            ],
            'email' => [
                'configured' => $emailConfigured,
                'status' => $emailConfigured ? 'success' : 'error'
            ]
        ];
    }

    /**
     * Check if email is properly configured
     */
    private function isEmailConfigured(): bool
    {
        $smtpHost = $this->settingRepository->get('email_smtp_host');
        $smtpUsername = $this->settingRepository->get('email_smtp_username');
        $fromEmail = $this->settingRepository->get('email_from_address');
        
        return !empty($smtpHost) && !empty($smtpUsername) && !empty($fromEmail);
    }

    /**
     * General settings page
     */
    public function general(): Response
    {
        $page = Page::make(__('settings.general'))
            ->breadcrumb('Home', '/cp')
            ->breadcrumb(__('settings.title'), '/cp/settings')
            ->breadcrumb(__('settings.general'));

        $settings = $this->settingRepository->getGeneralSettings();

        return Inertia::render('settings-general', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'settings' => $settings,
        ]);
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'general.store_name' => 'required|string|max:255',
            'general.store_description' => 'nullable|string',
            'general.store_email' => 'required|email',
            'general.store_phone' => 'nullable|string|max:20',
            'general.store_address' => 'nullable|string',
            'general.store_city' => 'nullable|string|max:100',
            'general.store_state' => 'nullable|string|max:100',
            'general.store_country' => 'required|string|max:2',
            'general.store_postal_code' => 'nullable|string|max:20',
            'general.timezone' => 'required|string',
            'general.currency' => 'required|string|max:3',
            'general.weight_unit' => 'required|string|in:kg,g,lb,oz',
            'general.dimension_unit' => 'required|string|in:cm,m,in,ft',
        ]);

        try {
            $this->settingRepository->updateGeneralSettings($validated);

            return response()->json([
                'success' => true,
                'message' => __('settings.messages.general_updated'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('settings.messages.update_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Checkout settings page
     */
    public function checkout(): Response
    {
        $page = Page::make(__('settings.checkout'))
            ->breadcrumb('Home', '/cp')
            ->breadcrumb(__('settings.title'), '/cp/settings')
            ->breadcrumb(__('settings.checkout'));

        $settings = $this->settingRepository->getCheckoutSettings();

        return Inertia::render('settings-checkout', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'settings' => $settings,
        ]);
    }

    /**
     * Update checkout settings
     */
    public function updateCheckout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'checkout.guest_checkout_enabled' => 'boolean',
            'checkout.account_creation_required' => 'boolean',
            'checkout.terms_acceptance_required' => 'boolean',
            'checkout.newsletter_signup_enabled' => 'boolean',
            'checkout.order_notes_enabled' => 'boolean',
            'checkout.phone_required' => 'boolean',
            'checkout.company_field_enabled' => 'boolean',
        ]);

        try {
            $this->settingRepository->setMultiple($validated);

            return response()->json([
                'success' => true,
                'message' => __('settings.messages.checkout_updated'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('settings.messages.update_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Email settings page
     */
    public function email(): Response
    {
        $page = Page::make(__('settings.email'))
            ->breadcrumb('Home', '/cp')
            ->breadcrumb(__('settings.title'), '/cp/settings')
            ->breadcrumb(__('settings.email'));

        $settings = $this->settingRepository->getEmailSettings();

        return Inertia::render('settings-email', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'settings' => $settings,
        ]);
    }

    /**
     * Update email settings
     */
    public function updateEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email.order_confirmation_enabled' => 'boolean',
            'email.order_status_updates_enabled' => 'boolean',
            'email.shipping_confirmation_enabled' => 'boolean',
            'email.customer_welcome_enabled' => 'boolean',
            'email.low_stock_notifications_enabled' => 'boolean',
            'email.admin_notification_email' => 'nullable|email',
        ]);

        try {
            $this->settingRepository->setMultiple($validated);

            return response()->json([
                'success' => true,
                'message' => __('settings.messages.email_updated'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('settings.messages.update_error'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
