# Changelog

## [2.0.0-beta.3](https://github.com/getformwork/formwork/releases/tag/2.0.0-beta.3)

**Enhancements**

- **Require PHP >= 8.3**
- **Add Polish and Ukrainian translation** (🤖 AI generated, reviews are welcome)
- Add default Cache-Control header
- Prevent session_start() from setting cache headers
- Handle conditional requests
- Add cache headers to assets
- By default make page requests conditional if cache is enabled
- Add `autoEtag` and `autoLastModified` params to `FileResponse` constructor
- Save response time by making errors controller lazy
- Lazily-load dynamic field vars
- Avoid tracking visit to maintenance, unpublished and not routable pages
- Update .htaccess and server script to allow access to .well-known
- Improve route patterns and order
- Replace `mimeTypes.extensionTypes` with closure to increase response speed
- Move some strings out from panel translations
- Refine serve command output
- Decouple classes and traits from `App::instance()`
- Avoid defining global `$formwork` variable
- Remove unused `DataGetter` and `DataSetter` classes
- Finalize several classes and privatize methods and properties
- Limit search and filtering to word boundaries
- Avoid reporting gd warnings
- Use content folder last modified time to determine cached response
- Touch content folder when clearing pages cache
- Update page last modified time after changes to files

**Bug fixes**

- Fix dropdowns scrolling by keyboard
- Avoid setting unnecessary alpha flag to VP8X chunks
- Copy original image resampled to avoid GIF images trasparency issues
- Avoid artifacts on images with alpha channel
- Avoid transforms propagation to avoid unnecessary image creation
- Fix relative URI used instead of absolute in `Request::validateReferer()`
- Convert palette images to truecolor before outputting WebP

## [2.0.0-beta.2](https://github.com/getformwork/formwork/releases/tag/2.0.0-beta.2)

**Breaking Changes**

- **Users, roles and statistics folders moved to sites/**

**Enhancements**

- **Add content history to panel**
- **Add live preview to panel**
- **Implement new Markdown editor**
- **Translate scheme and templates titles**
- **Allow theme switching based on `prefers-color-scheme` change**
- **Implement file metadata**
- **Add page info cards by hovering on page icons**
- **Add descriptions to publish and visibility-related fields**
- **Send `FileResponse` splitted chunkwise and according to the Range request header to improve performance with large files**
- **Allow `HEAD` requests**
- **Add slug field type**
- Add `Role` class
- Move Info to Tools section
- Add `csrfToken` service alias
- Allow and filter POST requests to site pages
- Avoid using special fields for page parent and template
- Improve file upload field
- Add `AbstractCollection::flatten()`, `AbstractCollection::union()`, `AbstractCollection::intersection()`, `AbstractCollection::difference()` and `AbstractCollection::find()`
- Allow index-only call to `AbstractCollection::slice()`
- Add utility methods to `PageCollection`
- Add `site.path` to config
- Fix `Debug::dump()` dumping before sendig headers
- Check panel assets presence on boot
- Add the possibility to delete user image
- Use attribute `ReadonlyModelProperty` to control Model::set() write access
- Add `Page::videos()` and `Page::media()`
- Allow defining icon in page schemes options
- Change default session durations to 2 
- Load only video metadata in thumbnails
- Add preview size to dimensionless images
- Add `AbstractController::forward()` to forward requests to other controllers
- Move authentication logic to `User`
- Add `Page::save()` method
- Add `Field::isReadonly()`
- Add `InvalidValueException` to handle exceptions in model setters

**Security**

- **Add `Sanitizer` class to sanitize Markdown and SVG output**

## [2.0.0-beta.1](https://github.com/getformwork/formwork/releases/tag/2.0.0-beta.1)
As the upcoming version 2.0.0 is a major release and the code has been extensively rewritten (~ 900 commits), here are listed only the most notable changes (the list may not be exhaustive and could change):

**Breaking Changes**

- **PHP version requirement raised to >= 8.2**
- **Application architecture rewritten for version 2.0, `Formwork` class has been replaced with `App` class, which is the app container**
- **Config, content and templates folder moved to sites/**
- **admin folder, route and even `Admin/*` classes renamed to panel or `Panel/*`**
- Classes from admin/ moved to formwork/src/Panel
- Rewritten logic between schemes, fields and pages
- Rewritten `Page`, `Site` and related classes
- camelCase is now enforced in all keys and PascalCase in class name now is consistent
- HTTP related classes moved to `Formwork\Http` namespace and now are services handled by the container
- Rewritten `Router` class

**Enhancements**

- **Improved Administration Panel with a better page editing experience**
- **Added file info views and thumbnails options to display files in the panel**
- **New Statistics and Backup views**
- **Improved Panel UI on mobile devices**
- **Added debug option to get stack traces during developement**
- **Added `serve` command to test Formwork even without a webserver**
- **Added informative errors during bootstrap**
- Fields now have their own methods defined in formwork/fields
- Fields now support dynamic variables by suffixing properties with `@`
- Added `AbstractCollection` and `Collection` classes to better handle data
- Added `Constraint` class to check data
- Added `Interpolator` class
- Added improved image-related class in the namespace `Formwork\Image` with a better image transformation API and support for reading color profiles and EXIF metadata
- Transformed images are now cached
- Added `Debug` and `CodeDumper` classes

**Security**

- **Added `content.safeMode` system option** (enabled by default) to escape HTML in Markdown content
- **Fields in the Panel are now accurately escaped**
- Escaped page titles and tags in default templates

## [1.13.2](https://github.com/getformwork/formwork/releases/tag/1.13.2) - [0.6.9](https://github.com/getformwork/formwork/releases/tag/0.6.9)
➡️ Read previous [CHANGELOG.md](https://github.com/getformwork/formwork/blob/1.x/CHANGELOG.md) on the `1.x` branch.