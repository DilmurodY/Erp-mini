import { i18n } from '@/utils/modules/i18n';

export const columns = {
    id: {
        show: true,
        title: i18n.t('message.n'),
        sortable: true,
        column: 'id'
    },
    user_id: {
        show: true,
        title: i18n.t('message.user'),
        sortable: true,
        column: 'user_id'
    },
    total_price: {
        show: true,
        title: i18n.t('message.amount'),
        sortable: false,
        column: 'total_price',
    },
    share_percentage: {
        show: true,
        title: i18n.t('message.share_percentage'),
        sortable: false,
        column: 'share_percentage'
    },
    is_active: {
        show: true,
        title: i18n.t('message.active'),
        sortable: true,
        column: 'is_active',
    },
    phone: {
        show: true,
        title: i18n.t('message.phone'),
        sortable: false,
        column: 'phone',
    },
    email: {
        show: true,
        title: i18n.t('message.email'),
        sortable: false,
        column: 'email',
    },
    comment: {
        show: true,
        title: i18n.t('message.comment'),
        sortable: true,
        column: 'comment',
    },
    created_at: {
        show: true,
        title: i18n.t('message.date of creation'),
        sortable: true,
        column: 'created_at'
    },
    updated_at: {
        show: false,
        title: i18n.t('message.Changed'),
        sortable: true,
        column: 'updated_at'
    },
    settings: {
        show: true,
        title: i18n.t('message.settings'),
        sortable: false,
        column: 'settings'
    }
};
