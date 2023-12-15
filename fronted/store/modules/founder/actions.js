import {
  index,
  inventory,
  show,
  store,
  update,
  destroy,
  getSharePercentageOfFounder
} from "@/api/founders";

export const actions = {
  index({ commit }, params = {}) {
    return new Promise((resolve, reject) => {
      index(params)
        .then((res) => {
          commit("SET_LIST", res.data.result.data.founders);
          commit("SET_CHARTER_CAPITAL", res.data.result.data.charter_capital);
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

  inventory({ commit }, params = {}) {
    return new Promise((resolve, reject) => {
      inventory(params)
        .then((res) => {
          commit("SET_INVENTORY", res.data.result.data.founders);
          resolve(res.data);
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
          commit("SET_MODEL", res.data.result.data.founder);
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

  getSharePercentageOfFounder({ commit }, founder_id) {
        return new Promise((resolve, reject) => {
            getSharePercentageOfFounder(founder_id).then(res => {
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
