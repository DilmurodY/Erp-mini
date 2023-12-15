import { filter } from "./properties/filter";
import { sort } from "../properties/sort";
import { pagination } from "../properties/pagination";
import { model } from "./properties/model";

export const mutations = {
  SET_LIST: (state, founders) => (state.list = founders),
  SET_CHARTER_CAPITAL: (state, charter_capital) => (state.charter_capital = charter_capital),
  SET_INVENTORY: (state, founders) => (state.inventory = founders),
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
  },
  EMPTY_MODEL: (state) => {
    state.model = JSON.parse(JSON.stringify(model));
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
