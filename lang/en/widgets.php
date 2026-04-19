<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chart axis / shared
    |--------------------------------------------------------------------------
    */
    'charts' => [
        'axis_date_format' => 'M j',
        'dataset_revenue' => 'Revenue',
        'dataset_orders' => 'Orders',
        'dataset_units_sold' => 'Units sold',
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin panel dashboard
    |--------------------------------------------------------------------------
    */
    'admin' => [
        'overview_description' => 'Last 30 days compared with the previous 30 days. Sparklines show daily activity.',

        'stats' => [
            'revenue' => 'Revenue',
            'orders' => 'Orders',
            'new_customers' => 'New customers',
            'active_stores' => 'Active stores',
            'branches' => 'Branches',
            'products' => 'Products',
        ],

        'pending' => 'pending',
        'new_item' => 'new',

        'trend_increase' => ':value% increase',
        'trend_decrease' => ':value% decrease',
        'trend_flat' => 'Flat vs prior period',

        'orders_stat_description' => ':pending pending · :trend',
        'snapshot_new_trend' => ':count new · :trend',

        'money_m' => '$:amountM',
        'money_k' => '$:amountk',
        'money_full' => '$:amount',

        'charts' => [
            'revenue_trend_heading' => 'Revenue trend',
            'revenue_trend_description' => 'Daily order revenue across all stores.',
            'orders_by_status_heading' => 'Orders by status',
            'orders_by_status_description' => 'All-time distribution of order statuses.',
            'top_products_heading' => 'Top products',
            'top_products_description' => 'Units sold across the catalog (order line items).',
            'branch_performance_heading' => 'Branch performance',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Store panel dashboard
    |--------------------------------------------------------------------------
    */
    'store' => [
        'period' => [
            'today' => 'Today',
            'week' => 'This week',
            'month' => 'This month',
            'default' => 'Last 30 days',
        ],

        'stats' => [
            'revenue' => 'Revenue',
            'orders' => 'Orders',
            'customers' => 'Customers',
            'new_customers' => 'New customers',
            'products' => 'Products',
            'total_products' => 'Total products',
        ],

        'revenue_vs' => [
            'today' => 'vs yesterday: :change%',
            'week' => 'vs last week: :change%',
            'default' => 'vs previous period: :change%',
        ],

        'orders_vs' => [
            'today' => 'Pending: :pending | vs yesterday: :change%',
            'week' => 'Pending: :pending | vs last week: :change%',
            'default' => 'Pending: :pending | vs previous period: :change%',
        ],

        'customers_vs' => [
            'today' => 'New: :count | vs yesterday: :change%',
            'week' => 'New: :count | vs last week: :change%',
            'default' => 'New: :count | vs previous period: :change%',
        ],

        'products_active' => 'Active: :count',

        'charts' => [
            'revenue_trend_heading' => 'Revenue trend',
            'orders_by_status_heading' => 'Orders by status',
            'top_products_heading' => 'Top products',
            'branch_performance_heading' => 'Branch performance',
        ],
    ],
];
