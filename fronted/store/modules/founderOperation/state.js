import { columns } from './properties/columns'
import { filter } from './properties/filter'
import { sort } from './../properties/sort'
import { pagination } from './../properties/pagination'
import { model } from "./properties/model";
import { rules } from "./properties/rules";

export const state = {
    list: [],
    operationTypes: [],
    investmentTypes: [],
    outputTypes: [],
    product_items: [],
    material_items: [],
    model: JSON.parse(JSON.stringify(model)),
    columns: columns,
    defaultColumns: JSON.parse(JSON.stringify(columns)),
    filter: filter,
    pagination: JSON.parse(JSON.stringify(pagination)),
    sort: JSON.parse(JSON.stringify(sort)),
    rules: rules,
};
