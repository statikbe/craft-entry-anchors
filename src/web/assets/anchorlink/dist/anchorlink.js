/**
 * Anchor Link fields (see statikbe\entryanchors\fields\AnchorLink):
 * - the anchor follows the title while the block isn't published yet (settings.follow), and is kept after that;
 * - "Edit" opens the anchor editor; "Apply" (or Enter) makes the edited anchor the anchor and stops it from following the
 *   title, "Cancel" (or Escape) leaves it as it was;
 * - the copy button copies the link to the block, `#anchor` or the page URL + `#anchor` (the copyFormat setting).
 * Slugs come from the server (the same slugs the front end uses), fetched while typing, so the copy happens right in the click.
 */
(function ($) {
  Craft.EntryAnchorsAnchorLink = Garnish.Base.extend({
    $title: null,
    $anchor: null,
    $anchorLine: null,
    $anchorLabel: null,
    $anchorField: null,
    $anchorInput: null,
    $apply: null,
    $copy: null,
    follow: false,
    anchor: null,
    request: 0,
    timeout: null,

    init(id, settings) {
      this.settings = settings;
      this.follow = !!settings.follow;
      this.$title = $('#' + id);
      this.$anchor = $('#' + id + '-anchor');
      this.$anchorLine = $('#' + id + '-anchor-line');
      this.$anchorLabel = $('#' + id + '-anchor-label');
      this.$anchorField = $('#' + id + '-anchor-field');
      this.$anchorInput = $('#' + id + '-anchor-input');
      this.$apply = $('#' + id + '-anchor-apply');
      this.$copy = $('#' + id + '-copy');

      this.anchor = this.$anchor.val() || null;
      this.setCopyEnabled(!!this.anchor);

      this.addListener(this.$title, 'input', () => {
        if (this.follow || !this.$anchor.val()) {
          clearTimeout(this.timeout);
          this.setCopyEnabled(false);
          this.timeout = setTimeout(() => this.slugify(this.$title.val()).then((anchor) => this.setAnchor(anchor)), 300);
        }
        this.updateVisibility();
      });

      this.addListener($('#' + id + '-anchor-edit'), 'click', 'openEditor');
      this.addListener(this.$apply, 'click', 'applyEdit');
      this.addListener($('#' + id + '-anchor-cancel'), 'click', 'closeEditor');
      this.addListener(this.$anchorInput, 'keydown', (event) => {
        // Enter applies the anchor instead of submitting the entry form, Escape cancels (and keeps a slideout open)
        if (event.keyCode === Garnish.RETURN_KEY) {
          event.preventDefault();
          this.applyEdit();
        } else if (event.keyCode === Garnish.ESC_KEY) {
          event.preventDefault();
          event.stopPropagation();
          this.closeEditor();
        }
      });

      this.addListener(this.$copy, 'click', 'copy');
      this.updateVisibility();
    },

    openEditor() {
      this.$anchorInput.val(this.$anchor.val());
      this.$anchorField.removeClass('hidden');
      this.updateVisibility();
      this.$anchorInput.trigger('focus').trigger('select');
    },

    closeEditor() {
      this.$anchorField.addClass('hidden');
      this.updateVisibility();
    },

    /**
     * The edited anchor becomes the anchor (as a slug; empty falls back to the title) and is kept from now on.
     */
    applyEdit() {
      this.$apply.addClass('loading').prop('disabled', true);
      this.slugify(this.$anchorInput.val() || this.$title.val())
        .then((anchor) => {
          this.follow = false;
          this.setAnchor(anchor);
          this.closeEditor();
        })
        .finally(() => this.$apply.removeClass('loading').prop('disabled', false));
    },

    /**
     * The anchor for a text, from the server (the same slugs the front end uses). Resolves with null for an empty text, and
     * only for the last request.
     */
    slugify(text) {
      text = (text || '').trim();
      const request = ++this.request;

      if (!text) {
        return Promise.resolve(null);
      }

      return Craft.sendActionRequest('GET', 'entry-anchors/anchors/slug', { params: { title: text } }).then((response) => {
        if (request !== this.request) {
          return new Promise(() => {});
        }
        return response.data.slug || null;
      });
    },

    setAnchor(anchor) {
      this.anchor = anchor;
      this.$anchorLabel.text('#' + (anchor || ''));
      this.setCopyEnabled(!!anchor);
      // Trigger change so the element editor saves a draft
      this.$anchor.val(anchor || '').trigger('change');
    },

    setCopyEnabled(enabled) {
      this.$copy.prop('disabled', !enabled).toggleClass('disabled', !enabled);
    },

    updateVisibility() {
      const hasTitle = !!this.$title.val().trim();
      // The anchor line, unless the anchor editor is open
      this.$anchorLine.toggleClass('hidden', !hasTitle || !this.$anchorField.hasClass('hidden'));
    },

    copy() {
      if (!this.anchor) {
        return;
      }

      const anchor = '#' + this.anchor;
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
