import {
  index,
  show,
  store,
  update,
  destroy,
  getOperationTypes,
  getInvestmentTypes,
  getOutputTypes,
  getInvestmentHistoryOfFounder,
  deleteNomenclature,
  getNomenclatures
} from "@/api/founderOperations";

export const actions = {
  index({ commit }, params = {}) {
    return new Promise((resolve, reject) => {
      index(params)
        .then((res) => {
          commit("SET_LIST", res.data.result.data.founderOperations);
          commit("UPDATE_PAGINATION", {
            key: "total",
            value: res.data.result.data.pagination.total,
          });
          resolve(res.data.result);
        })
        .catch((err) => {
          reject(err.response.data);
        });
    });
  },

  show({ commit }, id) {
    return new Promise((resolve, reject) => {
      show(id)
        .then((res) => {
          commit("SET_MODEL", res.data.result.data.founderOperation);
          resolve(res.data.result);
        })
        .catch((err) => {
          reject(err.response.data);
        });
    });
  },

  store({ commit }, data) {
    return new Promise((resolve, reject) => {
      store(data)
        .then((res) => {
          resolve(res.data.result);
        })
        .catch((err) => {
          reject(err.response.data);
        });
    });
  },

  update({ commit }, data) {
    return new Promise((resolve, reject) => {
      update(data)
        .then((res) => {
          resolve(res.data.result);
        })
        .catch((err) => {
          reject(err.response.data);
        });
    });
  },

  destroy({ commit }, id) {
    return new Promise((resolve, reject) => {
      destroy(id)
        .then((res) => {
          resolve(res.data.result);
        })
        .catch((err) => {
          reject(err.response.data);
        });
    });
  },

  getOperationTypes({ commit }) {
        return new Promise((resolve, reject) => {
            getOperationTypes().then(res => {
                commit("SET_OPERATION_TYPES", res.data.result.data.operationTypes);
                resolve(res.data.result)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    getInvestmentTypes({ commit }) {
        return new Promise((resolve, reject) => {
            getInvestmentTypes().then(res => {
                commit("SET_INVESTMENT_TYPES", res.data.result.data.investmentTypes);
                resolve(res.data.result)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    getOutputTypes({ commit }) {
        return new Promise((resolve, reject) => {
            getOutputTypes().then(res => {
                commit("SET_OUTPUT_TYPES", res.data.result.data.outputTypes);
                resolve(res.data.result)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    getInvestmentHistoryOfFounder({ commit }, founder_id) {
        return new Promise((resolve, reject) => {
            getInvestmentHistoryOfFounder(founder_id).then(res => {
                resolve(res.data.result)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },

    deleteNomenclature({ commit }, data) {
        return new Promise((resolve, reject) => {
            deleteNomenclature(data).then((res) => {
                resolve(res.data.result);
            })
            .catch((err) => {
                reject(err.response.data);
            });
        });
    },

    getNomenclatures({ commit }, operation_id) {
        return new Promise((resolve, reject) => {
            getNomenclatures(operation_id).then(res => {
                resolve(res.data.result)
            }).catch(err => {
                reject(err.response.data)
            })
        })
    },


  updateSort({ commit }, sort) {
    commit("SET_SORT", sort);
  },

  updateFilter({ commit }, filter) {
    commit("SET_FILTER", JSON.parse(JSON.stringify(filter)));
  },

  updateColumn({ commit }, obj) {
    commit("UPDATE_COLUMN", obj);
  },

  updatePagination({ commit }, pagination) {
    commit("UPDATE_PAGINATION", pagination);
  },
  refreshData({ commit }) {
    return new Promise((resolve, reject) => {
      commit("REFRESH");
      resolve();
    });
  },
  empty({ commit }) {
    return new Promise((resolve, reject) => {
      commit("EMPTY_MODEL");
      resolve();
    });
  },
};
