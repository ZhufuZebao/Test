<template>
  <ul class="paging">
    <!-- first -->
    <li :class="['paging-item', 'paging-item--first', {'paging-item--disabled' : current === 1}]"
        @click="first"><<
    </li>
    <!-- prev -->
    <li :class="['paging-item', 'paging-item--prev', {'paging-item--disabled' : current === 1}]"
        @click="prev"><
    </li>
    <li :class="['paging-item', 'paging-item--more']"
        v-if="showPrevMore">...
    </li>
    <li v-if="pageType === 'select'">
      <select @change="go(goPage)" v-model="goPage">
        <option v-for="pager in pages" :key="pager" :value="pager">{{pager}}</option>
      </select>
    </li>
    <li v-else :class="['paging-item', {'paging-item--current' : current === pager}]"
        v-for="pager in pageNumbers" @click="go(pager)">{{ pager }}
    </li>
    <li :class="['paging-item', 'paging-item--more']"
        v-if="showNextMore">...
    </li>
    <!-- next -->
    <li :class="['paging-item', 'paging-item--next', {'paging-item--disabled' : current === pages}]"
        @click="next">>
    </li>
    <!-- last -->
    <li :class="['paging-item', 'paging-item--last', {'paging-item--disabled' : current === pages}]"
        @click="last">>>
    </li>
  </ul>
</template>

<script>
  export default {
    name: 'Pagination',
    props: {
      // ページングの表示数量
      pageShow: {
        type: Number,
        default: 5
      },
      pageCurrent: {
        type: Number,
        default: 1
      },
      pageSize: {
        type: Number,
        default: 10
      },
      total: {
        type: Number,
        default: 1
      },
      pageType: {
        default: 'select'
      },
    },
    methods: {
      prev() {
        if (this.current > 1) {
          this.go(this.current - 1);
        }
      },
      next() {
        if (this.current < this.pages) {
          this.go(this.current + 1);
        }
      },
      first() {
        if (this.current !== 1) {
          this.go(1);
        }
      },
      last() {
        if (this.current !== this.pages) {
          this.go(this.pages);
        }
      },
      go(page) {
        // 現在のページではない場合、遷移
        if (this.current !== page) {
          this.current = page;
          this.goPage = page;
          this.$emit('change', this.current);
        }
      }
    },
    computed: {
      pages() {
        return Math.ceil(this.size / this.limit)
      },
      // ページ計算
      pageNumbers() {
        const array = [];
        const pageShow = this.pageShow;
        const pageCount = this.pages;
        let current = this.current;
        const _offset = (pageShow - 1) / 2;

        const offset = {
          start: current - _offset,
          end: current + _offset
        };

        //-1, 3
        if (offset.start < 1) {
          offset.end = offset.end + (1 - offset.start);
          offset.start = 1
        }
        if (offset.end > pageCount) {
          offset.start = offset.start - (offset.end - pageCount);
          offset.end = pageCount
        }
        if (offset.start < 1) offset.start = 1;

        // this.showPrevMore = (offset.start > 1)
        // this.showNextMore = (offset.end < pageCount)

        for (let i = offset.start; i <= offset.end; i++) {
          array.push(i)
        }
        return array
      }
    },
    data() {
      return {
        current: this.pageCurrent,
        goPage: 1,
        limit: this.pageSize,
        size: this.total || 1,
        showPrevMore: false,
        showNextMore: false
      }
    },
    watch: {
      pageCurrent(val) {
        this.current = val || 1
      },
      pageSize(val) {
        this.limit = val || 10
      },
      total(val) {
        this.size = val || 1
      }
    }
  }
</script>
<style scoped lang="scss">
  .paging {
    display: inline-block;
    padding: 0;
    margin: 1rem 0;
    font-size: 0;
    list-style: none;
    user-select: none;

    li {
      display: inline;
    }
    select {
      font-size: 14px;
      position: relative;
      padding: 6px 12px;
      line-height: 1.42857143;
      text-decoration: none;
      border: 1px solid #ccc;
      background-color: #fff;
      margin-left: -1px;
      cursor: pointer;
      color: #0275d8;
   }

    > .paging-item {
      display: inline;
      font-size: 14px;
      position: relative;
      padding: 6px 12px;
      line-height: 1.42857143;
      text-decoration: none;
      border: 1px solid #ccc;
      background-color: #fff;
      margin-left: -1px;
      cursor: pointer;
      color: #0275d8;

      &:first-child {
        margin-left: 0;
      }

      &:hover {
        background-color: #f0f0f0;
        color: #0275d8;
      }

      &.paging-item--disabled,
      &.paging-item--more {
        background-color: #fff;
        color: #505050;
      }

      &.paging-item--disabled {
        cursor: not-allowed;
        opacity: .75;
      }

      &.paging-item--more,
      &.paging-item--current {
        cursor: default;
      }

      &.paging-item--current {
        background-color: #0275d8;
        color: #fff;
        position: relative;
        z-index: 1;
        border-color: #0275d8;
      }
    }
  }
</style>
