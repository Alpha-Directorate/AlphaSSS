Translation_Jobs.listing.views.abstract.CollectionView = Backbone.View.extend({
    el: ".groups",
    initialize: function (options) {
        var self = this;
        self.options = options;

        self.$el.data('view', self);

        self.render(options);
    },
    render: function (option) {
        var self = this,
            options = _.extend({}, option),
            append = option.append_to;

        self._cleanBeforeRender(self.$el);

        self.fragment = document.createDocumentFragment();

        self.appendModelElement(options);

        self.$el.append(self.fragment);

        return self;
    },
    appendModelElement: function (opt) {

        var self = this, view, el, options;

        self.model.each(function (model) {

            options = {
                model: model
            };

            view = new Translation_Jobs.listing.views.ListingGroupView(options);

            el = view.render(options).el;

            jQuery(el).undelegate('#group-previous-jobs', 'click');
            jQuery(el).undelegate('#group-remaining-jobs', 'click');
            self.fragment.appendChild(el);
        }, self);

        return this;
    },
    /*
     ** remove all the children view to clean event queue
     */
    _cleanBeforeRender: function (el) {
        var self = this;

        el.find('tr', 'tbody').each(function (i, v) {
            if (jQuery(v).data('view')) {
                self._cleanBeforeRender(jQuery(v));
                jQuery(v).data('view').remove();
            }

        });
    }
});
