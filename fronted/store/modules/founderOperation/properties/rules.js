import { i18n } from '@/utils/modules/i18n';

export const rules = {
    founder_id: [
        { required: true, message: i18n.t('message.This field is required'), trigger: 'blur' }
    ],
    start_date: [
        { required: true, message: i18n.t('message.This field is required'), trigger: 'blur' }
    ],
    operation_type_id: [
        { required: true, message: i18n.t('message.This field is required'), trigger: 'blur' }
    ],
    currency_id: [
        { required: true, message: i18n.t('message.This field is required'), trigger: 'blur' }
    ],
};
