# Social links

The **Social links** CMS element displays numbered link rows with a label, username and arrow. The whole row opens its configured URL in a new tab. An eyebrow and heading can be added optionally.

Each link has three input fields:

- `label`: visible label, such as Instagram.
- `username`: optional username or supporting text, such as @artist.
- `url`: complete HTTP or HTTPS URL.

Use the up and down arrows to reorder entries. Their order is stored directly in the array; no position field is needed. All entries can be removed. The CMS element is omitted when there are no valid links.

Foundation's form builder automatically uses this editor for entity fields named `socialLinks`, in the `general` tab and `contact` section. The fieldset is available as `fieldsetsConfig['social-links']`. Ticket Vendor's Artist administration uses this mapping as well.
