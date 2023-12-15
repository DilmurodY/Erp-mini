export const getters = {
    list: state => state.list,
    operationTypes: state => state.operationTypes,
    investmentTypes: state => state.investmentTypes,
    outputTypes: state => state.outputTypes,
    product_items: state => state.product_items,
    material_items: state => state.material_items,
    columns: state => state.columns,
    filter: state => state.filter,
    pagination: state => state.pagination,
    sort: state => state.sort,
    model: state => state.model,
    rules: state => state.rules,
    form: state => {
        return {
            id: state.model.id,
            founder_id: state.model.founder ? state.model.founder.id : null,
            start_date: state.model.start_date ? state.model.start_date : new Date(),
            operation_type_id: state.model.operation_type ? state.model.operation_type.id : null,
            investment_type_id: state.model.investment_type ? state.model.investment_type.id : null,
            output_type_id: state.model.output_type ? state.model.output_type.id : null,
            total_price: state.model.total_price,
            currency_id: state.model.currency ? state.model.currency.id : null,
            rate: state.model.rate,
        }
    }
};
