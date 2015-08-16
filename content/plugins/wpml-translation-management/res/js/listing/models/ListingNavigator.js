/*globals Backbone, Translation_Jobs, _ */
(function () {
	"use strict";

	Translation_Jobs.listing.models.ListingNavigator = Backbone.Model.extend(
		{
			initialize:       function () {
				_.bindAll(this, 'setup_pagination');
				this.bind('per_page_change', this.setup_pagination);
				this.bind('item_count_change', this.setup_pagination);
			},
			defaults:         {
				show_all: false,
				per_page: 10,
				page:     1,
				items:    0
			},
			get_type:         function () {
				return 'Navigator';
			},
			to_page:          function (page) {
				if (page >= Math.min(this.get('pages'), 1) && page <= this.get('pages')) {
					this.set('page', page, {silent: true});
					this.trigger('page_change');
				}
			},
			display_count:    function () {
				if (this.get('per_page') > this.defaults.per_page) {
					this.set('per_page', this.defaults.per_page, {silent: true});
					this.set('show_all', false, {silent: true});
					this.trigger('per_page_change');
					return;
				} else {
					this.set('per_page', this.get('items'), {silent: true});
					this.set('show_all', true, {silent: true});
				}
				this.trigger('per_page_change');
			},
			item_count:       function (item_count) {
				this.set('items', item_count, {silent: true});
				this.trigger('item_count_change');
			},
			setup_pagination: function () {
				this.set('pages', Math.ceil(this.get('items') / this.get('per_page')), {silent: true});
				if (this.get('pages') < this.get('page')) {
					this.set('page', this.get('pages'), {silent: true});
					this.trigger('pagination_changed');
				} else if (this.get('page') < 1 && this.get('pages') > 0) {
					this.set('page', 1, {silent: true});
					this.trigger('pagination_changed');
				}
			}
		}
	);
}());
