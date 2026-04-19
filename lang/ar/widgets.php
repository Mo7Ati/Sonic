<?php

return [

    'charts' => [
        'axis_date_format' => 'j M',
        'dataset_revenue' => 'الإيرادات',
        'dataset_orders' => 'الطلبات',
        'dataset_units_sold' => 'الوحدات المباعة',
    ],

    'admin' => [
        'overview_description' => 'آخر 30 يومًا مقارنة بالـ 30 يومًا السابقة. الخطوط البيانية تعرض النشاط اليومي.',

        'stats' => [
            'revenue' => 'الإيرادات',
            'orders' => 'الطلبات',
            'new_customers' => 'عملاء جدد',
            'active_stores' => 'متاجر نشطة',
            'branches' => 'الفروع',
            'products' => 'المنتجات',
        ],

        'pending' => 'قيد الانتظار',
        'new_item' => 'جديد',

        'trend_increase' => 'زيادة :value٪',
        'trend_decrease' => 'انخفاض :value٪',
        'trend_flat' => 'مستقر مقارنة بالفترة السابقة',

        'orders_stat_description' => ':pending قيد الانتظار · :trend',
        'snapshot_new_trend' => ':count جديد · :trend',

        'money_m' => '$:amountM',
        'money_k' => '$:amountk',
        'money_full' => '$:amount',

        'charts' => [
            'revenue_trend_heading' => 'اتجاه الإيرادات',
            'revenue_trend_description' => 'إيرادات الطلبات اليومية عبر جميع المتاجر.',
            'orders_by_status_heading' => 'الطلبات حسب الحالة',
            'orders_by_status_description' => 'توزيع حالات الطلبات (كل الأوقات).',
            'top_products_heading' => 'أفضل المنتجات',
            'top_products_description' => 'الوحدات المباعة عبر الكتالوج (بنود الطلب).',
            'branch_performance_heading' => 'أداء الفروع',
        ],
    ],

    'store' => [
        'period' => [
            'today' => 'اليوم',
            'week' => 'هذا الأسبوع',
            'month' => 'هذا الشهر',
            'default' => 'آخر 30 يومًا',
        ],

        'stats' => [
            'revenue' => 'الإيرادات',
            'orders' => 'الطلبات',
            'customers' => 'العملاء',
            'new_customers' => 'عملاء جدد',
            'products' => 'المنتجات',
            'total_products' => 'إجمالي المنتجات',
        ],

        'revenue_vs' => [
            'today' => 'مقارنة بالأمس: :change٪',
            'week' => 'مقارنة بالأسبوع الماضي: :change٪',
            'default' => 'مقارنة بالفترة السابقة: :change٪',
        ],

        'orders_vs' => [
            'today' => 'قيد الانتظار: :pending | مقارنة بالأمس: :change٪',
            'week' => 'قيد الانتظار: :pending | مقارنة بالأسبوع الماضي: :change٪',
            'default' => 'قيد الانتظار: :pending | مقارنة بالفترة السابقة: :change٪',
        ],

        'customers_vs' => [
            'today' => 'جدد: :count | مقارنة بالأمس: :change٪',
            'week' => 'جدد: :count | مقارنة بالأسبوع الماضي: :change٪',
            'default' => 'جدد: :count | مقارنة بالفترة السابقة: :change٪',
        ],

        'products_active' => 'نشط: :count',

        'charts' => [
            'revenue_trend_heading' => 'اتجاه الإيرادات',
            'orders_by_status_heading' => 'الطلبات حسب الحالة',
            'top_products_heading' => 'أفضل المنتجات',
            'branch_performance_heading' => 'أداء الفروع',
        ],
    ],
];
