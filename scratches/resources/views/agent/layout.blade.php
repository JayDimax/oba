@php
  $agentNavItems = [
    ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'agent.dashboard'],
    ['label' => 'Bet History', 'icon' => 'history', 'route' => 'agent.bet.history'],
    ['label' => 'Winning Bets', 'icon' => 'trophy', 'route' => 'agent.winning'],
    ['label' => 'Results', 'icon' => 'list', 'route' => 'agent.results'],
    ['label' => 'Reports', 'icon' => 'file-text', 'route' => 'agent.reports'],
    ['label' => 'Collections', 'icon' => 'archive', 'route' => 'agent.collections'],
    ['label' => 'Support', 'icon' => 'help-circle', 'route' => 'agent.support'],
    ['label' => 'Settings', 'icon' => 'settings', 'route' => 'agent.settings'],
  ];
@endphp

<x-sidebar
  :navItems="$agentNavItems"
  :userName="auth()->user()->name"
  :userCode="'@' . auth()->user()->agent_code"
  :userContact="auth()->user()->agent?->phone"
/>
