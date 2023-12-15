import { filter } from "./properties/filter";
import { sort } from "../properties/sort";
import { pagination } from "../properties/pagination";
import { model } from "./properties/model";

export const mutations = {
  SET_LIST: (state, founderOperations) => (state.list = founderOperations),
  SET_OPERATION_TYPES: (state, operationTypes) => (state.operationTypes = operationTypes),
  SET_INVESTMENT_TYPES: (state, investmentTypes) => (state.investmentTypes = investmentTypes),
  SET_OUTPUT_TYPES: (state, outputTypes) => (state.outputTypes = outputTypes),
  SET_FILTER: (state, filter) => (state.filter = filter),
  SET_PAGINATION: (state, pagination) => (state.pagination = pagination),
  SET_SORT: (state, sort) => (state.sort = sort),
  UPDATE_COLUMN: (state, obj) => {
    state.columns[obj.key].show = obj.value;
  },
  SET_COLUMNS(state, payload){
      state.columns = payload
  },
  UPDATE_SORT: (state, sort) => {
    state.sort[sort.column] = sort.order;
  },
  UPDATE_PAGINATION: (state, pagination) => {
    state.pagination[pagination.key] = pagination.value;
  },
  SET_MODEL: (state, model) => {
        state.model = model;

        state.product_items = [];
        if (model.product_items) {
            _.forEach(model.product_items, function(item) {
                state.product_items.push({
                    id: item.id,
                    product: item.product,
                    quantity: parseFloat(item.quantity),
                    price: parseFloat(item.price),
                    currency: item.currency,
                    rate: item.currency ? (item.currency.reverse ? _.round(1 / item.rate, 20) : item.rate) : 1,
                })
            })
        }

        state.material_items = [];
        if (model.material_items) {
            _.forEach(model.material_items, function(item) {
                state.material_items.push({
                    id: item.id,
                    material: item.material,
                    quantity: parseFloat(item.quantity),
                    price: parseFloat(item.price),
                    currency: item.currency,
                    rate: item.currency ? (item.currency.reverse ? _.round(1 / item.rate, 20) : item.rate) : 1,
                })
            })
        }
  },
  EMPTY_MODEL: (state) => {
    state.model = JSON.parse(JSON.stringify(model));
    state.product_items = [];
    state.material_items = [];
  },
  REFRESH: (state) => {
    state.filter = JSON.parse(JSON.stringify(filter));
    state.sort = JSON.parse(JSON.stringify(sort));
    state.pagination = JSON.parse(JSON.stringify(pagination));
  },
  EMPTY_LIST: (state) => {
    state.list = [];
  },
};
