import { i18n } from '@/utils/modules/i18n';

export const columns = {
    id: {
        show: true,
        title: i18n.t('message.n'),
        sortable: true,
        column: 'id'
    },
    founder_id: {
        show: true,
        title: i18n.t('message.founder'),
        sortable: true,
        column: 'founder_id'
    },
    start_date: {
        show: true,
        title: i18n.t('message.Begin date'),
        sortable: true,
        column: 'start_date',
    },
    operation_type_id: {
        show: true,
        title: i18n.t('message.operation_type'),
        sortable: true,
        column: 'operation_type_id'
    },
    investment_type_id: {
        show: true,
        title: i18n.t('message.investment_type'),
        sortable: true,
        column: 'investment_type_id',
    },
    output_type_id: {
        show: true,
        title: i18n.t('message.output_type'),
        sortable: true,
        column: 'output_type_id',
    },
    total_price: {
        show: true,
        title: i18n.t('message.Amount'),
        sortable: true,
        column: 'total_price',
    },
    currency_id: {
        show: true,
        title: i18n.t('message.currency'),
        sortable: true,
        column: 'currency_id',
    },
    rate: {
        show: true,
        title: i18n.t('message.rate'),
        sortable: true,
        column: 'rate',
    },
    performed: {
        show: true,
        title: i18n.t('message.performed'),
        sortable: false,
        column: 'performed',
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
