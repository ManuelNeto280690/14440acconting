@extends('layouts.tenant')

@section('title', 'Planos de Assinatura')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Planos de Assinatura</h1>
            <p class="text-gray-600">Escolha o plano ideal para suas necessidades</p>
        </div>
        <div class="flex gap-3 mt-4 sm:mt-0">
            <a href="{{ route('tenant.billing.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                Voltar ao Faturamento
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Current Plan Info -->
    @if($currentSubscription)
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-blue-900">Plano Atual: {{ $currentSubscription->plan->name }}</h3>
                    <p class="text-blue-700 text-sm">
                        R$ {{ number_format($currentSubscription->plan->price, 2, ',', '.') }}/{{ $currentSubscription->plan->billing_cycle === 'monthly' ? 'mês' : 'ano' }}
                        @if($currentSubscription->ends_at)
                            • {{ $currentSubscription->status === 'cancelled' ? 'Expira em' : 'Próxima cobrança em' }} {{ $currentSubscription->ends_at->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Billing Cycle Toggle -->
    <div class="flex justify-center mb-8">
        <div class="bg-gray-100 p-1 rounded-lg">
            <div class="flex">
                <button type="button" 
                        id="monthly-btn"
                        onclick="toggleBillingCycle('monthly')"
                        class="px-6 py-2 rounded-md font-medium transition-all duration-200 billing-cycle-btn active">
                    Mensal
                </button>
                <button type="button" 
                        id="yearly-btn"
                        onclick="toggleBillingCycle('yearly')"
                        class="px-6 py-2 rounded-md font-medium transition-all duration-200 billing-cycle-btn">
                    Anual
                    <span class="ml-2 bg-green-500 text-white text-xs px-2 py-1 rounded-full">-20%</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse($plans as $plan)
            @php
                $isCurrentPlan = $currentSubscription && $currentSubscription->plan_id === $plan->id;
                $canUpgrade = !$currentSubscription || $plan->price > $currentSubscription->plan->price;
                $canDowngrade = $currentSubscription && $plan->price < $currentSubscription->plan->price;
                $features = is_array($plan->features) ? $plan->features : json_decode($plan->features, true) ?? [];
            @endphp
            
            <div class="bg-white rounded-lg shadow-lg overflow-hidden {{ $isCurrentPlan ? 'ring-2 ring-blue-500' : '' }} {{ $plan->is_popular ? 'relative' : '' }}">
                @if($plan->is_popular)
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                        <span class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-4 py-1 rounded-full text-sm font-medium">
                            Mais Popular
                        </span>
                    </div>
                @endif

                <div class="p-6">
                    <!-- Plan Header -->
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ $plan->description }}</p>
                        
                        <!-- Price -->
                        <div class="mb-4">
                            <div class="monthly-price {{ $plan->billing_cycle === 'yearly' ? 'hidden' : '' }}">
                                <span class="text-3xl font-bold text-gray-900">
                                    R$ {{ number_format($plan->price, 2, ',', '.') }}
                                </span>
                                <span class="text-gray-600">/mês</span>
                            </div>
                            <div class="yearly-price {{ $plan->billing_cycle === 'monthly' ? 'hidden' : '' }}">
                                @php
                                    $yearlyPrice = $plan->price * 12 * 0.8; // 20% discount
                                    $monthlyEquivalent = $yearlyPrice / 12;
                                @endphp
                                <span class="text-3xl font-bold text-gray-900">
                                    R$ {{ number_format($yearlyPrice, 2, ',', '.') }}
                                </span>
                                <span class="text-gray-600">/ano</span>
                                <div class="text-sm text-green-600 mt-1">
                                    R$ {{ number_format($monthlyEquivalent, 2, ',', '.') }}/mês
                                </div>
                            </div>
                        </div>

                        @if($isCurrentPlan)
                            <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                Plano Atual
                            </span>
                        @endif
                    </div>

                    <!-- Features -->
                    <div class="space-y-3 mb-6">
                        @if(isset($features['max_documents']))
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">
                                    {{ $features['max_documents'] === -1 ? 'Documentos ilimitados' : number_format($features['max_documents']) . ' documentos/mês' }}
                                </span>
                            </div>
                        @endif

                        @if(isset($features['max_storage']))
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">
                                    {{ $features['max_storage'] }} GB de armazenamento
                                </span>
                            </div>
                        @endif

                        @if(isset($features['max_api_calls']))
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">
                                    {{ $features['max_api_calls'] === -1 ? 'API calls ilimitadas' : number_format($features['max_api_calls']) . ' API calls/mês' }}
                                </span>
                            </div>
                        @endif

                        @if(isset($features['integrations']) && $features['integrations'])
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">Integrações avançadas</span>
                            </div>
                        @endif

                        @if(isset($features['ai_processing']) && $features['ai_processing'])
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">Processamento com IA</span>
                            </div>
                        @endif

                        @if(isset($features['priority_support']) && $features['priority_support'])
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">Suporte prioritário</span>
                            </div>
                        @endif

                        @if(isset($features['custom_branding']) && $features['custom_branding'])
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">Marca personalizada</span>
                            </div>
                        @endif

                        @if(isset($features['advanced_analytics']) && $features['advanced_analytics'])
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-gray-700">Analytics avançado</span>
                            </div>
                        @endif
                    </div>

                    <!-- Action Button -->
                    <div class="text-center">
                        @if($isCurrentPlan)
                            <button type="button" 
                                    disabled
                                    class="w-full bg-gray-300 text-gray-500 px-6 py-3 rounded-lg font-medium cursor-not-allowed">
                                Plano Atual
                            </button>
                        @elseif(!$currentSubscription)
                            <form method="POST" action="{{ route('tenant.billing.change-plan') }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit" 
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                                    Escolher Plano
                                </button>
                            </form>
                        @elseif($canUpgrade)
                            <form method="POST" action="{{ route('tenant.billing.change-plan') }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit" 
                                        class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200"
                                        onclick="return confirm('Deseja fazer upgrade para este plano? A diferença será cobrada proporcionalmente.')">
                                    Fazer Upgrade
                                </button>
                            </form>
                        @elseif($canDowngrade)
                            <form method="POST" action="{{ route('tenant.billing.change-plan') }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit" 
                                        class="w-full bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200"
                                        onclick="return confirm('Deseja fazer downgrade para este plano? A alteração será aplicada no próximo ciclo de cobrança.')">
                                    Fazer Downgrade
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <div class="text-gray-400 mb-4">
                    <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum Plano Disponível</h3>
                <p class="text-gray-600">Não há planos de assinatura disponíveis no momento.</p>
            </div>
        @endforelse
    </div>

    <!-- FAQ Section -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Perguntas Frequentes</h3>
        
        <div class="space-y-4">
            <div class="border-b border-gray-200 pb-4">
                <button type="button" 
                        class="flex justify-between items-center w-full text-left faq-toggle"
                        onclick="toggleFaq(1)">
                    <span class="font-medium text-gray-900">Posso alterar meu plano a qualquer momento?</span>
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="faq-1" class="hidden mt-3 text-gray-600">
                    Sim, você pode fazer upgrade ou downgrade do seu plano a qualquer momento. Para upgrades, a diferença será cobrada proporcionalmente. Para downgrades, a alteração será aplicada no próximo ciclo de cobrança.
                </div>
            </div>

            <div class="border-b border-gray-200 pb-4">
                <button type="button" 
                        class="flex justify-between items-center w-full text-left faq-toggle"
                        onclick="toggleFaq(2)">
                    <span class="font-medium text-gray-900">Como funciona o período de teste?</span>
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="faq-2" class="hidden mt-3 text-gray-600">
                    Novos usuários recebem 14 dias de teste gratuito com acesso completo às funcionalidades do plano escolhido. Não há cobrança durante o período de teste.
                </div>
            </div>

            <div class="border-b border-gray-200 pb-4">
                <button type="button" 
                        class="flex justify-between items-center w-full text-left faq-toggle"
                        onclick="toggleFaq(3)">
                    <span class="font-medium text-gray-900">O que acontece se eu exceder os limites do meu plano?</span>
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="faq-3" class="hidden mt-3 text-gray-600">
                    Se você exceder os limites do seu plano, entraremos em contato para discutir um upgrade. Não há interrupção imediata do serviço, mas recomendamos ajustar o plano para evitar limitações futuras.
                </div>
            </div>

            <div class="pb-4">
                <button type="button" 
                        class="flex justify-between items-center w-full text-left faq-toggle"
                        onclick="toggleFaq(4)">
                    <span class="font-medium text-gray-900">Posso cancelar minha assinatura?</span>
                    <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="faq-4" class="hidden mt-3 text-gray-600">
                    Sim, você pode cancelar sua assinatura a qualquer momento. O acesso continuará até o final do período já pago. Seus dados serão mantidos por 30 dias após o cancelamento.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentBillingCycle = 'monthly';

function toggleBillingCycle(cycle) {
    currentBillingCycle = cycle;
    
    // Update button states
    document.querySelectorAll('.billing-cycle-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(cycle + '-btn').classList.add('active');
    
    // Show/hide prices
    document.querySelectorAll('.monthly-price').forEach(el => {
        el.classList.toggle('hidden', cycle !== 'monthly');
    });
    document.querySelectorAll('.yearly-price').forEach(el => {
        el.classList.toggle('hidden', cycle !== 'yearly');
    });
}

function toggleFaq(id) {
    const content = document.getElementById('faq-' + id);
    const button = content.previousElementSibling;
    const icon = button.querySelector('svg');
    
    content.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}

// CSS for active billing cycle button
document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .billing-cycle-btn {
            color: #6b7280;
        }
        .billing-cycle-btn.active {
            background-color: #ffffff;
            color: #1f2937;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
    `;
    document.head.appendChild(style);
});
</script>
@endsection