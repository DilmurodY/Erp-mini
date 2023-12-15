export const getters = {
    list: state => state.list,
    charter_capital: state => state.charter_capital,
    inventory: state => state.inventory,
    columns: state => state.columns,
    filter: state => state.filter,
    pagination: state => state.pagination,
    sort: state => state.sort,
    model: state => state.model,
    rules: state => state.rules,
    form: state => {
        return {
            id: state.model.id,
            user_id: state.model.user ? state.model.user.id : null,
            is_active: state.model.is_active ? true : false,
            comment: state.model.comment,
        }
    }
};
