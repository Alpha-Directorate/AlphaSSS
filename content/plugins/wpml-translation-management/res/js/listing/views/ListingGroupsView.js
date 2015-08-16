/*jshint browser:true, devel:true */
/*global jQuery, Backbone, Translation_Jobs, head, ajaxurl, _ */
(function () {
    "use strict";

    Translation_Jobs.listing.views.ListingGroupsView = Translation_Jobs.listing.views.abstract.CollectionView.extend(
        {
            tagName: 'tbody',
            el: '.wpml-translation-management-jobs',
            initialize: function (options) {
                var self = this;
                
                _.bindAll(self, 'render', 'afterRender');

                self.render = _.wrap(
                    self.render, function (render, args) {
                        render(args);
                        _.defer(self.afterRender, _.bind(self.afterRender, self));
                        return self;
                    }
                );

                self.options = options;
                self.$el.data('view', self);

                Translation_Jobs.listing.views.abstract.CollectionView.prototype.initialize.call(self, options);
            },
            events: {
                "click #group-previous-jobs": "prev_page",
                "click #group-remaining-jobs": "next_page"
            },
            prev_page: function (e) {
                var self = this;
                self.options.vent.trigger('prev_page', e);
                self.options.vent.off();
                self.off();
            },
            next_page: function (e) {
                var self = this;
                self.options.vent.trigger('next_page', e);
                self.options.vent.off();
                self.off();
            },
            render: function (option) {
                var self = this, options = _.extend({}, option);

                self._cleanBeforeRender(self.$el);

                self.fragment = document.createDocumentFragment();

                self.appendModelElement(options);

                self.$el.find('.listing-page-table-list').remove();

                self.$el.find('thead').after(self.fragment);

                return self;
            },
            afterRender: function () {

            }
            
        }
    );
}());
