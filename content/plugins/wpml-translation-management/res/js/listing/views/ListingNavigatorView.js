/*globals jQuery, Backbone, Translation_Jobs, _ */
(function () {
    "use strict";
    Translation_Jobs.listing.views.ListingNavigatorView = Backbone.View.extend(
        {
            el: '.navigator',
            initialize: function (options) {
                var self = this;
                _.bindAll(self, "render");

                _.bindAll(self, "next_page");
                _.bindAll(self, "prev_page");

                _.bindAll(self, 'render', 'afterRender');

                this.model.bind('page_change', this.cleanup);
                this.model.bind('per_page_change', this.cleanup);

                self.render = _.wrap(
                    self.render, function (render, args) {
                        render(args);
                        _.defer(self.afterRender, _.bind(self.afterRender, self));
                        return self;
                    }
                );

                self.options = options;
                self.$el.data('view', self);

                self.options.vent.bind("next_page", self.next_page);
                self.options.vent.bind("prev_page", self.prev_page);
            },
            events: {
                "click #icl_jobs_show_all": "update_pagination",
                "click .js-nav-prev-page": "prev_page",
                "click .js-nav-next-page": "next_page",
                "click .js-nav-next-page-arrow": "next_page",
                "click .js-nav-prev-page-arrow": "prev_page",
                "click .js-nav-before-prev-page": "before_prev_page",
                "click .js-nav-after-next-page": "after_next_page",
                "click .js-nav-first-page": "first_page",
                "click .js-nav-last-page": "last_page"
            },
            render: function (option) {

                var self = this, options = option || {};

                var name = self.model.get_type().toLowerCase();
                self.template = _.template(jQuery('#table-listing-' + name).html());

                self.$el.html(self.template(_.extend(self.model.toJSON(), options)));

                var items = this.model.get('items');
                var page = this.model.get('page');
                var pages = this.model.get('pages');
                var per_page = this.model.get('per_page');

                var icl_jobs_show_all = jQuery("#icl_jobs_show_all");
                if (items < this.model.defaults.per_page) {
                    icl_jobs_show_all.remove();
                } else {
                    if (!this.model.get('show_all')) {
                        icl_jobs_show_all.text((icl_jobs_show_all.text().replace('%s', items)));
                    } else {
                        icl_jobs_show_all.text(icl_jobs_show_all.text().replace('%s', this.model.defaults.per_page));
                    }
                }
                var nav_before_prev_page = jQuery(".js-nav-before-prev-page");
                var nav_prev_page = jQuery(".js-nav-prev-page");
                var nav_after_next_page = jQuery(".js-nav-after-next-page");
                var nav_next_page = jQuery(".js-nav-next-page");
                var nav_last_page = jQuery(".js-nav-last-page");
                if (pages <= 1 || items === 0) {
                    if (pages < 1) {
                        jQuery(".displaying-num").hide();
                    }
                    nav_prev_page.hide();
                    nav_next_page.hide();
                    jQuery(".js-nav-prev-page-arrow").hide();
                    jQuery(".js-nav-next-page-arrow").hide();
                    nav_before_prev_page.hide();
                    nav_after_next_page.hide();
                    jQuery(".js-nav-first-page").hide();
                    nav_last_page.hide();
                    jQuery(".js-nav-right-dots").hide();
                    jQuery(".js-nav-left-dots").hide();
                } else {
                    nav_before_prev_page.text(page - 2);
                    nav_prev_page.text(page - 1);
                    nav_after_next_page.text(page + 2);
                    nav_next_page.text(page + 1);

                    nav_last_page.text(pages);
                    jQuery(".page-numbers-current").text(page);
                    if (page === pages) {
                        nav_next_page.hide();
                        jQuery(".js-nav-next-page-arrow").hide();
                    } else if (page === 1) {
                        nav_prev_page.hide();
                        jQuery(".js-nav-prev-page-arrow").hide();
                    }
                    if (page + 1 >= pages) {
                        nav_after_next_page.hide();
                    }
                    if (page < 3) {
                        nav_before_prev_page.hide();
                    }
                    if (page + 3 >= pages) {
                        jQuery(".js-nav-right-dots").hide();
                        if (page + 2 >= pages) {
                            nav_last_page.hide();
                        }
                    }
                    if (page - 3 <= 1) {
                        jQuery(".js-nav-left-dots").hide();
                        if (page - 2 <= 1) {
                            jQuery(".js-nav-first-page").hide();
                        }
                    }
                }

                return self;
            },
            cleanup: function () {
                return this;
            },
            afterRender: function () {
            },
            update_pagination: function (e) {
                if (typeof e.preventDefault !== 'undefined') {
                    e.preventDefault();
                }
                this.model.display_count();
                this.cleanup();
                return this;
            },
            next_page: function (e) {
                if (typeof e.preventDefault !== 'undefined') {
                    e.preventDefault();
                }

                this.model.to_page(this.model.get('page') + 1);

                return false;
            },
            prev_page: function (e) {
                if (typeof e.preventDefault !== 'undefined') {
                    e.preventDefault();
                }

                this.model.to_page(this.model.get('page') - 1);

                return false;
            },
            after_next_page: function (e) {
                if (typeof e.preventDefault !== 'undefined') {
                    e.preventDefault();
                }

                this.model.to_page(this.model.get('page') + 2);

                return false;
            },
            before_prev_page: function (e) {
                if (typeof e.preventDefault !== 'undefined') {
                    e.preventDefault();
                }

                this.model.to_page(this.model.get('page') - 2);

                return false;
            },
            first_page: function (e) {
                if (typeof e.preventDefault !== 'undefined') {
                    e.preventDefault();
                }
                this.model.to_page(1);

                return false;
            },
            last_page: function (e) {
                if (typeof e.preventDefault !== 'undefined') {
                    e.preventDefault();
                }
                this.model.to_page(this.model.get('pages'));

                return false;
            }
        }
    );
}());
