/**
 * Copy button of Anchor Link fields: copies the link to the block, `#slug` or the page URL + `#slug` (the copyFormat setting).
 * The slug comes from the server (the same slug the front end uses for the id) and is fetched while typing, so the copy
 * itself happens right in the click.
 */
(function ($) {
  Craft.EntryAnchorsAnchorLink = Garnish.Base.extend({
    $input: null,
    $copy: null,
    slug: null,
    request: 0,

    init(id, settings) {
      this.settings = settings;
      this.$input = $('#' + id);
      this.$copy = $('#' + id + '-copy');

      this.updateSlug();
      this.addListener(this.$input, 'input', () => {
        clearTimeout(this.timeout);
        this.$copy.prop('disabled', true);
        this.timeout = setTimeout(() => this.updateSlug(), 300);
      });
      this.addListener(this.$copy, 'click', 'copy');
    },

    updateSlug() {
      const title = this.$input.val().trim();
      const request = ++this.request;
      this.slug = null;
      this.$copy.prop('disabled', true);

      if (!title) {
        return;
      }

      Craft.sendActionRequest('GET', 'entry-anchors/anchors/slug', { params: { title } }).then((response) => {
        // Only the answer to the last request counts
        if (request === this.request) {
          this.slug = response.data.slug;
          this.$copy.prop('disabled', !this.slug);
        }
      });
    },

    copy() {
      if (!this.slug) {
        return;
      }

      const anchor = '#' + this.slug;
      const text = this.settings.copyFormat === 'anchor' || !this.settings.url ? anchor : this.settings.url + anchor;

      const copied = () => Craft.cp.displayNotice(this.settings.copied);

      if (!navigator.clipboard) {
        this.copyFallback(text) ? copied() : Craft.cp.displayError(this.settings.failed);
        return;
      }

      navigator.clipboard.writeText(text).then(copied, () => {
        this.copyFallback(text) ? copied() : Craft.cp.displayError(this.settings.failed);
      });
    },

    /**
     * When the Clipboard API isn't there or isn't allowed (http, some browsers' permissions).
     */
    copyFallback(text) {
      const $textarea = $('<textarea>', { readonly: true, css: { position: 'fixed', top: 0, left: 0, opacity: 0 } })
        .val(text)
        .appendTo(document.body);
      $textarea[0].select();
      let copied = false;
      try {
        copied = document.execCommand('copy');
      } catch (e) {
        copied = false;
      }
      $textarea.remove();
      this.$copy.trigger('focus');

      return copied;
    },
  });
})(jQuery);
