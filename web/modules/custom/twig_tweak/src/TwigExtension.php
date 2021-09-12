<?php

namespace Drupal\twig_tweak;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Block\TitleBlockPluginInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\Markup;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\media\MediaInterface;
use Drupal\media\Plugin\media\Source\OEmbedInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

use Auth0\SDK\JWTVerifier;
use Auth0\SDK\Auth0;
use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Helpers\State\SessionStateHandler;
use Auth0\SDK\Store\SessionStore;
use GuzzleHttp\Client;

/**
 * Twig extension with some useful functions and filters.
 *
 * Dependencies are not injected for performance reason.
 */
class TwigExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    $context_options = ['needs_context' => TRUE];
    $all_options = ['needs_environment' => TRUE, 'needs_context' => TRUE];

    return [
      // - Drupal View -
      //
      // @code
      //   {{ drupal_view('who_s_new', 'block_1') }}
      // @endcode
      new TwigFunction('drupal_view', 'views_embed_view'),

      // - Drupal View Result -
      //
      // @code
      //   {{ drupal_view('who_s_new', 'block_1') }}
      // @endcode
      new TwigFunction('drupal_view_result', 'views_get_view_result'),

      // - Drupal Block -
      //
      // In order to list all registered plugin IDs fetch them with block plugin
      // manager. With Drush it can be done like follows:
      // @code
      //   drush ev "print_r(array_keys(\Drupal::service('plugin.manager.block')->getDefinitions()));"
      // @endcode
      //
      // @code
      //   {# Print block using default configuration. #}
      //   {{ drupal_block('system_branding_block') }}
      //
      //   {# Print block using custom configuration. #}
      //   {{ drupal_block('system_branding_block', {label: 'Branding', use_site_name: false})
      //
      //   {# Bypass block.html.twig theming. #}
      //   {{ drupal_block('system_branding_block', wrapper=false) }}
      // @endcode
      //
      // @see https://www.drupal.org/node/2964457#block-plugin
      new TwigFunction('drupal_block', [$this, 'drupalBlock']),

      // - Drupal Region -
      //
      // @code
      //   {# Print 'Sidebar First' region of the default site theme. #}
      //   {{ drupal_region('sidebar_first') }}
      //
      //   {# Print 'Sidebar First' region of Bartik theme. #}
      //   {{ drupal_region('sidebar_first', 'bartik') }}
      // @endcode
      new TwigFunction('drupal_region', [$this, 'drupalRegion']),

      // - Drupal Entity -
      //
      // @code
      //   {# Print a content block which ID is 1. #}
      //   {{ drupal_entity('block_content', 1) }}
      //
      //   {# Print a node's teaser. #}
      //   {{ drupal_entity('node', 123, 'teaser') }}
      //
      //   {# Print Branding block which was previously disabled on #}
      //   {# admin/structure/block page. #}
      //   {{ drupal_entity('block', 'bartik_branding', check_access=false) }}
      // @endcode
      new TwigFunction('drupal_entity', [$this, 'drupalEntity']),

      // - Drupal Entity Form -
      //
      // @code
      //   {# Print edit form for node 1. #}
      //   {{ drupal_entity_form('node', 1) }}
      //
      //   {# Print add form for Article content type. #}
      //   {{ drupal_entity_form('node', values={type: 'article'}) }}
      //
      //   {# Print user register form. #}
      //   {{ drupal_entity_form('user', NULL, 'register', check_access=false) }}
      // @endcode
      new TwigFunction('drupal_entity_form', [$this, 'drupalEntityForm']),

      // - Drupal Field -
      //
      // @code
      //   {{ drupal_field('field_image', 'node', 1) }}
      //   {{ drupal_field('field_image', 'node', 1, 'teaser') }}
      //   {{ drupal_field('field_image', 'node', 1, {type: 'image_url', settings: {image_style: 'large'}}) }}
      // @endcode
      new TwigFunction('drupal_field', [$this, 'drupalField']),

      // - Drupal Menu -
      //
      // @code
      //   {{ drupal_menu('main') }}
      // @endcode
      new TwigFunction('drupal_menu', [$this, 'drupalMenu']),

      // - Drupal Form -
      //
      // @code
      //   {{ drupal_form('Drupal\\search\\Form\\SearchBlockForm') }}
      // @endcode
      new TwigFunction('drupal_form', [$this, 'drupalForm']),

      // - Drupal Image -
      //
      // @code
      //   {# Render image specified by file ID. #}
      //   {{ drupal_image(123) }}
      //
      //   {# Render image specified by file UUID. #}
      //   {{ drupal_image('9bb27144-e6b2-4847-bd24-adcc59613ec0') }}
      //
      //   {# Render image specified by file URI. #}
      //   {{ drupal_image('public://ocean.jpg') }}
      //
      //   {# Render image using 'thumbnail' image style and custom attributes. #}
      //   {{ drupal_image('public://ocean.jpg', 'thumbnail', {alt: 'The alternative text'|t, title: 'The title text'|t}) }}
      //
      //   {# Render responsive image. #}
      //   {{ drupal_image('public://ocean.jpg', 'wide', responsive=true) }}
      // @endcode
      new TwigFunction('drupal_image', [$this, 'drupalImage']),

      new TwigFunction('drupal_target_id_to_uri', [$this, 'drupalTargetIdToUri']),

      // - Drupal Token -
      //
      // @code
      //   {{ drupal_token('site:name') }}
      // @endcode
      new TwigFunction('drupal_token', [$this, 'drupalToken']),

      // - Drupal Config -
      //
      // @code
      //   {{ drupal_config('system.site', 'name') }}
      // @endcode
      new TwigFunction('drupal_config', [$this, 'drupalConfig']),

      // - Drupal Dump -
      //
      // @code
      //   {# Basic usage. #}
      //   {{ drupal_dump(var) }}
      //
      //   {# Same as above but shorter. #}
      //   {{ dd(var) }}
      //
      //   {# Dump all available variables for the current template. #}
      //   {{ dd() }}
      // @endcode
      new TwigFunction('drupal_dump', [$this, 'drupalDump'], $context_options),
      new TwigFunction('dd', [$this, 'drupalDump'], $context_options),

      // - Drupal Title -
      new TwigFunction('drupal_title', [$this, 'drupalTitle']),

      // - Drupal URL -
      //
      // @code
      //   {# Basic usage. #}
      //   {{ drupal_url('node/1) }}
      //
      //   {# Complex URL. #}
      //   {{ drupal_url('node/1', {query: {foo: 'bar'}, fragment: 'example', absolute: true}) }}
      // @endcode
      new TwigFunction('drupal_url', [$this, 'drupalUrl']),

      // - Drupal Link -
      //
      // @code
      //   {# It supports the same options as drupal_url(), plus attributes. #}
      //   {{ drupal_link('View'|t, 'node/1', {attributes: {target: '_blank'}}) }}
      //
      //   {# This link will only be shown for privileged users. #}
      //   {{ drupal_link('Example'|t, '/admin', check_access=true) }}
      // @endcode
      new TwigFunction('drupal_link', [$this, 'drupalLink']),

      // - Drupal Messages -
      new TwigFunction('drupal_messages', [$this, 'drupalMessages']),

      // - Drupal Breadcrumb -
      new TwigFunction('drupal_breadcrumb', [$this, 'drupalBreadcrumb']),

      // - Drupal Breakpoint -
      new TwigFunction('drupal_breakpoint', [$this, 'drupalBreakpoint'], $all_options),

      // - Contextual Links -
      //
      // @code
      //   {# Basic usage. #}
      //   <div class="contextual-region">
      //     {{ contextual_links('entity.view.edit_form:view=frontpage&display_id=feed_1') }}
      //     {{ drupal_view('frontpage') }}
      //   </div>
      //   {# Multiple links. #}
      //   <div class="contextual-region">
      //     {{ contextual_links('node:node=123|block_content:block_content=123') }}
      //     {{ content }}
      //   </div>
      // @endcode
      new TwigFunction('contextual_links', [$this, 'contextualLinks']),

      // Hai Custom - Get alias from node id
      new TwigFunction('get_alias_from_nid', [$this, 'getAliasFromNid']),

      // Hai Custom - Get alias from term id
      new TwigFunction('get_alias_from_tid', [$this, 'getAliasFromTid']),

      // Hai Custom - Get slug from term id
      new TwigFunction('get_slug_from_tid', [$this, 'getSlugFromTid']),

      // Hai Custom - Check user
      new TwigFunction('check_user', [$this, 'checkUser']),

      // Hai Custom - Custom url('<front>')
      new TwigFunction('urlf', [$this, 'urlFront']),

      // Hai Custom - Custom url('<front>')
      new TwigFunction('urlf2', [$this, 'urlFront2']),

      // Hai Custom
      new TwigFunction('url_img', [$this, 'urlImg']),

      // Hai Custom
      new TwigFunction('get_tid_page', [$this, 'getTidPage']),

      // Hai Custom
      new TwigFunction('lcode', [$this, 'getLangCode']),

      // Hai Custom
      new TwigFunction('capSK', [$this, 'getCaptchaSiteKey']),

      // Get site slogan
      new TwigFunction('site_slogan', [$this, 'siteSlogan']),

      // Hai Custom - Get translation of entity if the entity has translated
      new TwigFunction('entity_trans', [$this, 'entityTrans']),

      // Hai Custom - count total results of a view
      new TwigFunction('total_view_result', [$this, 'totalViewResult']),

      // Hai Custom - view result
      new TwigFunction('views_get_view_result', [$this, 'viewResult']),

      // Hai Custom - load term
      new TwigFunction('term', [$this, 'term']),

      // Hai Custom - load paragraph
      new TwigFunction('p_load', [$this, 'pLoad']),

      // Hai Custom - check 404/403 page
      new TwigFunction('error_page', [$this, 'errorPage']),

      // Hai Custom - authorize_url
      new TwigFunction('authorize_url', [$this, 'authorizeUrl']),

      // Hai Custom - get img on gcs
      new TwigFunction('gcs', [$this, 'gcs']),

      // Hai Custom - current route
      new TwigFunction('get_route', [$this, 'getRoute']),

      // Hai Custom - Get allowed-values of list text field
      new TwigFunction('allowed_value_label', [$this, 'allowedValueLabel']),

      // Hai Custom - Get path with langcode
      new TwigFunction('current_path', [$this, 'currentPath']),

      // Hai Custom - Get data of block
      new TwigFunction('block_values', [$this, 'blockValues']),

      // Hai Custom - Check field is NULL
      new TwigFunction('is_null', [$this, 'isNull']),

      // Hai Custom - Get value of field link to node
      new TwigFunction('value_ltn', [$this, 'valueLinkToNode']),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {

    $filters = [
      // - Token Replace -
      //
      // @code
      //   {# Basic usage. #}
      //   {{ '<h1>[site:name]</h1><div>[site:slogan]</div>'|token_replace }}
      //
      //   {# This is more suited to large markup (requires Twig >= 1.41). #}
      //   {% apply token_replace %}
      //     <h1>[site:name]</h1>
      //     <div>[site:slogan]</div>
      //   {% endapply %}
      // @endcode
      new TwigFilter('token_replace', [$this, 'tokenReplaceFilter']),

      // - Preg Replace -
      //
      // @code
      //   {{ 'Drupal - community plumbing!'|preg_replace('/(Drupal)/', '<b>$1</b>') }}
      // @endcode
      //
      // For simple string interpolation consider using built-in 'replace' or
      // 'format' Twig filters.
      new TwigFilter('preg_replace', [$this, 'pregReplaceFilter']),

      // - Image Style -
      //
      // @code
      //  {{ 'public://images/ocean.jpg'|image_style('thumbnail') }}
      // @endcode
      new TwigFilter('image_style', [$this, 'imageStyle']),

      // - Transliterate -
      //
      // @code
      //   {{ 'Привет!'|transliterate }}
      // @endcode
      new TwigFilter('transliterate', [$this, 'transliterate']),

      // - Check Markup -
      //
      // @code
      //   {{ '<b>bold</b> <strong>strong</strong>'|check_markup('restricted_html') }}
      // @endcode
      new TwigFilter('check_markup', [$this, 'checkMarkup']),

      // - Format Size -
      //
      // @code
      //   {{ 12345|format_size() }}
      // @endcode
      new TwigFilter('format_size', 'format_size'),

      // - Truncate -
      //
      // @code
      //   {{ 'Some long text'|truncate(10, true) }}
      // @endcode
      new TwigFilter('truncate', [$this, 'truncate']),

      // - View -
      //
      // @code
      //   {# Do not put this into node.html.twig template to avoid recursion. #}
      //   {{ node|view }}
      //   {{ node|view('teaser') }}
      //
      //   {{ node.field_image|view }}
      //   {{ node.field_image[0]|view }}
      //   {{ node.field_image|view('teaser') }}
      //   {{ node.field_image|view({settings: {image_style: 'thumbnail'}}) }}
      // @endcode
      new TwigFilter('view', [$this, 'view']),

      // - With -
      //
      // @code
      //   {# Set top level value. #}
      //   {{ content.field_image|with('#title', 'Photo'|t) }}
      //
      //   {# Set nested value. #}
      //   {{ content|with(['field_image', '#title'], 'Photo'|t) }}
      // @endcode
      new TwigFilter('with', [$this, 'with']),

      // - Children -
      //
      // @code
      //   <ul>
      //     {% for tag in content.field_tags|children %}
      //       <li>{{ tag }}</li>
      //     {% endfor %}
      //   </ul>
      // @endcode
      new TwigFilter('children', [$this, 'children']),

      // - File URI -
      //
      // When field item list passed the URI will be extracted from the first
      // item. In order to get URI of specific item specify its delta explicitly
      // using array notation.
      // @code
      //   {{ node.field_image|file_uri }}
      //   {{ node.field_image[0]|file_uri }}
      // @endcode
      //
      // Media fields are fully supported including OEmbed resources, in which
      // case it will return the URL to the resource, similar to the `file_url`
      // filter.
      // @code
      //   {{ node.field_media|file_uri }}
      // @endcode
      //
      // Useful to apply the `image_style` filter to Media fields.
      // Remember to check whether a URI is actually returned.
      // @code
      //   {% set media_uri = node.field_media|file_uri %}
      //   {% if media_uri is not null %}
      //     {{ media_uri|image_style('thumbnail') }}
      //   {% endif %}
      // @endcode
      new TwigFilter('file_uri', [$this, 'fileUri']),

      // - File URL -
      //
      // For string arguments it works similar to core file_url() Twig function.
      // @code
      //   {{ 'public://sea.jpg'|file_url }}
      // @endcode
      //
      // When field item list passed the URL will be extracted from the first
      // item. In order to get URL of specific item specify its delta explicitly
      // using array notation.
      // @code
      //   {{ node.field_image|file_url }}
      //   {{ node.field_image[0]|file_url }}
      // @endcode
      //
      // Media fields are fully supported including OEmbed resources.
      // @code
      //   {{ node.field_media|file_url }}
      // @endcode
      new TwigFilter('file_url', [$this, 'fileUrl']),
    ];

    if (Settings::get('twig_tweak_enable_php_filter')) {
      // - PHP -
      //
      // PHP filter is disabled by default. You can enable it in settings.php
      // file as follows:
      // @code
      //   $settings['twig_tweak_enable_php_filter'] = TRUE;
      // @endcode
      //
      // @code
      //   {{ 'return date('Y');'|php }}
      // @endcode
      //
      // Using PHP filter is discouraged as it may cause security implications.
      // In fact it is very rarely needed.
      //
      // The above code can be replaced with following.
      // @code
      //   {{ 'now'|date('Y') }}
      // @endcode
      $filters[] = new TwigFilter('php', [$this, 'phpFilter'], ['needs_context' => TRUE]);
    }
    return $filters;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'twig_tweak';
  }

  /**
   * Builds the render array for a block.
   *
   * @param mixed $id
   *   The string of block plugin to render.
   * @param array $configuration
   *   (optional) Pass on any configuration to the plugin block.
   * @param bool $wrapper
   *   (optional) Whether or not use block template for rendering.
   *
   * @return null|array
   *   A render array for the block or NULL if the block cannot be rendered.
   */
  public function drupalBlock($id, array $configuration = [], $wrapper = TRUE) {

    $configuration += ['label_display' => BlockPluginInterface::BLOCK_LABEL_VISIBLE];

    /** @var \Drupal\Core\Block\BlockPluginInterface $block_plugin */
    $block_plugin = \Drupal::service('plugin.manager.block')
      ->createInstance($id, $configuration);

    // Inject runtime contexts.
    if ($block_plugin instanceof ContextAwarePluginInterface) {
      $contexts = \Drupal::service('context.repository')->getRuntimeContexts($block_plugin->getContextMapping());
      \Drupal::service('context.handler')->applyContextMapping($block_plugin, $contexts);
    }

    $access = $block_plugin->access(\Drupal::currentUser(), TRUE);
    if (!$access->isAllowed()) {
      return;
    }

    // Title block needs special treatment.
    if ($block_plugin instanceof TitleBlockPluginInterface) {
      $request = \Drupal::request();
      $route_match = \Drupal::routeMatch();
      $title = \Drupal::service('title_resolver')->getTitle($request, $route_match->getRouteObject());
      $block_plugin->setTitle($title);
    }

    $build['content'] = $block_plugin->build();

    if ($block_plugin instanceof TitleBlockPluginInterface) {
      $build['content']['#cache']['contexts'][] = 'url';
    }

    if ($wrapper && !Element::isEmpty($build['content'])) {
      $build += [
        '#theme' => 'block',
        '#id' => $configuration['id'] ?? NULL,
        '#attributes' => [],
        '#contextual_links' => [],
        '#configuration' => $block_plugin->getConfiguration(),
        '#plugin_id' => $block_plugin->getPluginId(),
        '#base_plugin_id' => $block_plugin->getBaseId(),
        '#derivative_plugin_id' => $block_plugin->getDerivativeId(),
      ];
    }

    CacheableMetadata::createFromRenderArray($build)
      ->merge(CacheableMetadata::createFromObject($access))
      ->applyTo($build);

    return $build;
  }

  /**
   * Builds the render array of a given region.
   *
   * @param string $region
   *   The region to build.
   * @param string $theme
   *   (optional) The name of the theme to load the region. If it is not
   *   provided then default theme will be used.
   *
   * @return array
   *   A render array to display the region content.
   */
  public function drupalRegion($region, $theme = NULL) {

    $entity_type_manager = \Drupal::entityTypeManager();
    $blocks = $entity_type_manager->getStorage('block')->loadByProperties([
      'region' => $region,
      'theme'  => $theme ?: \Drupal::config('system.theme')->get('default'),
    ]);

    $view_builder = $entity_type_manager->getViewBuilder('block');

    $build = [];

    $entity_type = $entity_type_manager->getDefinition('block');
    $cache_metadata = (new CacheableMetadata())
      ->addCacheTags($entity_type->getListCacheTags())
      ->addCacheContexts($entity_type->getListCacheContexts());

    /* @var $blocks \Drupal\block\BlockInterface[] */
    foreach ($blocks as $id => $block) {
      $access = $block->access('view', NULL, TRUE);
      $cache_metadata = $cache_metadata->merge(CacheableMetadata::createFromObject($access));
      if ($access->isAllowed()) {
        $block_plugin = $block->getPlugin();
        if ($block_plugin instanceof TitleBlockPluginInterface) {
          $request = \Drupal::request();
          if ($route = $request->attributes->get(RouteObjectInterface::ROUTE_OBJECT)) {
            $block_plugin->setTitle(\Drupal::service('title_resolver')->getTitle($request, $route));
          }
        }
        $build[$id] = $view_builder->view($block);
      }
    }

    if ($build) {
      $build['#region'] = $region;
      $build['#theme_wrappers'] = ['region'];
    }
    $cache_metadata->applyTo($build);

    return $build;
  }

  /**
   * Returns the render array to represent and entity.
   *
   * @param string $entity_type
   *   The entity type.
   * @param mixed $id
   *   (optional) The ID of the entity to build.
   * @param string $view_mode
   *   (optional) The view mode that should be used to render the entity.
   * @param string $langcode
   *   (optional) For which language the entity should be rendered, defaults to
   *   the current content language.
   * @param bool $check_access
   *   (optional) Indicates that access check is required.
   *
   * @return null|array
   *   A render array for the entity or NULL if the entity does not exist.
   */
  public function drupalEntity($entity_type, $id = NULL, $view_mode = NULL, $langcode = NULL, $check_access = TRUE) {
    $entity_type_manager = \Drupal::entityTypeManager();
    if ($id) {
      $entity = $entity_type_manager->getStorage($entity_type)->load($id);
    }
    else {
      @trigger_error('Loading entities from route is deprecated in Twig Tweak 2.4 and will not be supported in Twig Tweak 3.0', E_USER_DEPRECATED);
      $entity = \Drupal::routeMatch()->getParameter($entity_type);
    }

    if ($entity) {
      $access = $check_access ? $entity->access('view', NULL, TRUE) : AccessResult::allowed();
      if ($access->isAllowed()) {
        $build = $entity_type_manager
          ->getViewBuilder($entity_type)
          ->view($entity, $view_mode, $langcode);
        CacheableMetadata::createFromRenderArray($build)
          ->merge(CacheableMetadata::createFromObject($entity))
          ->merge(CacheableMetadata::createFromObject($access))
          ->applyTo($build);
        return $build;
      }
    }
  }

  /**
   * Gets the built and processed entity form for the given entity type.
   *
   * @param string $entity_type
   *   The entity type.
   * @param mixed $id
   *   (optional) The ID of the entity to build. If empty then new entity will
   *   be created.
   * @param string $form_mode
   *   (optional) The mode identifying the form variation to be returned.
   * @param array $values
   *   (optional) An array of values to set, keyed by property name.
   * @param bool $check_access
   *   (optional) Indicates that access check is required.
   *
   * @return array
   *   The processed form for the given entity type and form mode.
   */
  public function drupalEntityForm($entity_type, $id = NULL, $form_mode = 'default', array $values = [], $check_access = TRUE) {
    $entity_storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    if ($id) {
      $entity = $entity_storage->load($id);
      $operation = 'update';
    }
    else {
      $entity = $entity_storage->create($values);
      $operation = 'create';
    }

    if ($entity) {
      $access = $check_access ? $entity->access($operation, NULL, TRUE) : AccessResult::allowed();
      if ($access->isAllowed()) {
        $build = \Drupal::service('entity.form_builder')->getForm($entity, $form_mode);
        CacheableMetadata::createFromRenderArray($build)
          ->merge(CacheableMetadata::createFromObject($entity))
          ->merge(CacheableMetadata::createFromObject($access))
          ->applyTo($build);
        return $build;
      }
    }
  }

  /**
   * Returns the render array for a single entity field.
   *
   * @param string $field_name
   *   The field name.
   * @param string $entity_type
   *   The entity type.
   * @param mixed $id
   *   The ID of the entity to render.
   * @param string $view_mode
   *   (optional) The view mode that should be used to render the field.
   * @param string $langcode
   *   (optional) Language code to load translation.
   * @param bool $check_access
   *   (optional) Indicates that access check is required.
   *
   * @return null|array
   *   A render array for the field or NULL if the value does not exist.
   */
  public function drupalField($field_name, $entity_type, $id = NULL, $view_mode = 'default', $langcode = NULL, $check_access = TRUE) {
    $entity_type_manager = \Drupal::entityTypeManager();

    if ($id) {
      $entity = $entity_type_manager->getStorage($entity_type)->load($id);
    }
    else {
      @trigger_error('Loading entities from route is deprecated in Twig Tweak 2.4 and will not be supported in Twig Tweak 3.0', E_USER_DEPRECATED);
      $entity = \Drupal::routeMatch()->getParameter($entity_type);
    }

    if ($entity) {
      $entity = \Drupal::service('entity.repository')->getTranslationFromContext($entity, $langcode);
      $access = $check_access ? $entity->access('view', NULL, TRUE) : AccessResult::allowed();
      if ($access->isAllowed()) {
        if (isset($entity->{$field_name})) {
          $build = $entity->{$field_name}->view($view_mode);
          CacheableMetadata::createFromRenderArray($build)
            ->merge(CacheableMetadata::createFromObject($access))
            ->merge(CacheableMetadata::createFromObject($entity))
            ->applyTo($build);
          return $build;
        }
      }
    }
  }

  /**
   * Returns the render array for Drupal menu.
   *
   * @param string $menu_name
   *   The name of the menu.
   * @param int $level
   *   (optional) Initial menu level.
   * @param int $depth
   *   (optional) Maximum number of menu levels to display.
   * @param bool $expand
   *   (optional) Expand all menu links.
   *
   * @return array
   *   A render array for the menu.
   */
  public function drupalMenu($menu_name, $level = 1, $depth = 0, $expand = FALSE) {
    /** @var \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree */
    $menu_tree = \Drupal::service('menu.link_tree');
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);

    // Adjust the menu tree parameters based on the block's configuration.
    $parameters->setMinDepth($level);
    // When the depth is configured to zero, there is no depth limit. When depth
    // is non-zero, it indicates the number of levels that must be displayed.
    // Hence this is a relative depth that we must convert to an actual
    // (absolute) depth, that may never exceed the maximum depth.
    if ($depth > 0) {
      $parameters->setMaxDepth(min($level + $depth - 1, $menu_tree->maxDepth()));
    }

    // If expandedParents is empty, the whole menu tree is built.
    if ($expand) {
      $parameters->expandedParents = [];
    }

    $tree = $menu_tree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);
    return $menu_tree->build($tree);
  }

  /**
   * Builds and processes a form for a given form ID.
   *
   * @param string $form_id
   *   The form ID.
   * @param ...
   *   Additional arguments are passed to form constructor.
   *
   * @return array
   *   A render array to represent the form.
   */
  public function drupalForm($form_id) {
    $callback = [\Drupal::formBuilder(), 'getForm'];
    return call_user_func_array($callback, func_get_args());
  }

  /**
   * Builds an image.
   *
   * @param mixed $property
   *   A property to identify the image.
   * @param string $style
   *   (optional) Image style.
   * @param array $attributes
   *   (optional) Image attributes.
   * @param bool $responsive
   *   (optional) Indicates that the provided image style is responsive.
   * @param bool $check_access
   *   (optional) Indicates that access check is required.
   *
   * @return array|null
   *   A render array to represent the image.
   */
  public function drupalImage($property, $style = NULL, array $attributes = [], $responsive = FALSE, $check_access = TRUE) {

    // Determine property type by its value.
    if (preg_match('/^\d+$/', $property)) {
      $property_type = 'fid';
    }
    elseif (Uuid::isValid($property)) {
      $property_type = 'uuid';
    }
    else {
      $property_type = 'uri';
    }

    $files = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties([$property_type => $property]);

    // To avoid ambiguity render nothing unless exact one image has been found.
    if (count($files) != 1) {
      return;
    }

    $file = reset($files);

    $access = $check_access ? $file->access('view', NULL, TRUE) : AccessResult::allowed();
    if (!$access->isAllowed()) {
      return;
    }

    $build = [
      '#uri' => $file->getFileUri(),
      '#attributes' => $attributes,
    ];

    if ($style) {
      if ($responsive) {
        $build['#type'] = 'responsive_image';
        $build['#responsive_image_style_id'] = $style;
      }
      else {
        $build['#theme'] = 'image_style';
        $build['#style_name'] = $style;
      }
    }
    else {
      $build['#theme'] = 'image';
    }

    CacheableMetadata::createFromRenderArray($build)
      ->merge(CacheableMetadata::createFromObject($access))
      ->applyTo($build);

    return $build;
  }

  public function drupalTargetIdToUri($target_id) {
    if ($target_id != null) {
      $file = File::load($target_id);
      $uri = $file->getFileUri();
      return $uri;
    }

    return '';
  }

  /**
   * Replaces a given tokens with appropriate value.
   *
   * @param string $token
   *   A replaceable token.
   * @param array $data
   *   (optional) An array of keyed objects. For simple replacement scenarios
   *   'node', 'user', and others are common keys, with an accompanying node or
   *   user object being the value. Some token types, like 'site', do not
   *   require any explicit information from $data and can be replaced even if
   *   it is empty.
   * @param array $options
   *   (optional) A keyed array of settings and flags to control the token
   *   replacement process.
   *
   * @return string
   *   The token value.
   *
   * @see \Drupal\Core\Utility\Token::replace()
   */
  public function drupalToken($token, array $data = [], array $options = []) {
    return \Drupal::token()->replace("[$token]", $data, $options);
  }

  /**
   * Retrieves data from a given configuration object.
   *
   * @param string $name
   *   The name of the configuration object to construct.
   * @param string $key
   *   A string that maps to a key within the configuration data.
   *
   * @return mixed
   *   The data that was requested.
   */
  public function drupalConfig($name, $key) {
    return \Drupal::config($name)->get($key);
  }

  /**
   * Dumps information about variables.
   *
   * @param array $context
   *   Variables from the Twig template.
   * @param mixed $variable
   *   (optional) The variable to dump.
   */
  public function drupalDump(array $context, $variable = NULL) {
    $var_dumper = '\Symfony\Component\VarDumper\VarDumper';
    if (class_exists($var_dumper)) {
      call_user_func($var_dumper . '::dump', func_num_args() == 1 ? $context : $variable);
    }
    else {
      trigger_error('Could not dump the variable because symfony/var-dumper component is not installed.', E_USER_WARNING);
    }
  }

  /**
   * Returns a title for the current route.
   *
   * @return array
   *   A render array to represent page title.
   */
  public function drupalTitle() {
    $title = \Drupal::service('title_resolver')->getTitle(
      \Drupal::request(),
      \Drupal::routeMatch()->getRouteObject()
    );
    $build['#markup'] = render($title);
    $build['#cache']['contexts'] = ['url'];
    return $build;
  }

  /**
   * Generates a URL from an internal path.
   *
   * @param string $user_input
   *   User input for a link or path.
   * @param array $options
   *   (optional) An array of options.
   * @param bool $check_access
   *   (optional) Indicates that access check is required.
   *
   * @return \Drupal\Core\Url|null
   *   A new Url object or null if the URL is not accessible.
   *
   * @see \Drupal\Core\Url::fromUserInput()
   */
  public function drupalUrl($user_input, array $options = [], $check_access = FALSE) {
    if (isset($options['langcode'])) {
      $language_manager = \Drupal::languageManager();
      if ($language = $language_manager->getLanguage($options['langcode'])) {
        $options['language'] = $language;
      }
    }
    if (!in_array($user_input[0], ['/', '#', '?'])) {
      $user_input = '/' . $user_input;
    }
    $url = Url::fromUserInput($user_input, $options);
    if (!$check_access || $url->access()) {
      return $url;
    }
  }

  /**
   * Generates a link from an internal path.
   *
   * @param string $text
   *   The text to be used for the link.
   * @param string $user_input
   *   User input for a link or path.
   * @param array $options
   *   (optional) An array of options.
   * @param bool $check_access
   *   (optional) Indicates that access check is required.
   *
   * @return \Drupal\Core\Link|null
   *   A new Link object or null of the URL is not accessible.
   *
   * @see \Drupal\Core\Link::fromTextAndUrl()
   */
  public function drupalLink($text, $user_input, array $options = [], $check_access = FALSE) {
    $url = $this->drupalUrl($user_input, $options, $check_access);
    if ($url) {
      // The text has been processed by twig already, convert it to a safe
      // object for the render system.
      // @see \Drupal\Core\Template\TwigExtension::getLink()
      if ($text instanceof \Twig_Markup) {
        $text = Markup::create($text);
      }
      return Link::fromTextAndUrl($text, $url);
    }
  }

  /**
   * Displays status messages.
   */
  public function drupalMessages() {
    return ['#type' => 'status_messages'];
  }

  /**
   * Builds the breadcrumb.
   */
  public function drupalBreadcrumb() {
    return \Drupal::service('breadcrumb')
      ->build(\Drupal::routeMatch())
      ->toRenderable();
  }

  /**
   * Builds contextual links.
   *
   * @param string $id
   *   A serialized representation of a #contextual_links property value array.
   *
   * @return array
   *   A renderable array representing contextual links.
   *
   * @see https://www.drupal.org/node/2133283
   */
  public function contextualLinks($id) {
    $build['#cache']['contexts'] = ['user.permissions'];
    if (\Drupal::currentUser()->hasPermission('access contextual links')) {
      $build['#type'] = 'contextual_links_placeholder';
      $build['#id'] = $id;
    }
    return $build;
  }
  
  /**
   * Custom get alias from node id
   */
  public function getAliasFromNid ($nid) {
    $url = base_path();
    $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $url_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
    $final_url = $url . $prefixes[$langcode] . $url_alias;
    return $final_url;
  }

  /**
   * Custom get alias from term id
   */
  public function getAliasFromTid ($tid) {
    $url = base_path();
    $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $url_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/'. $tid, $langcode);
    $final_url = $url . $prefixes[$langcode] . $url_alias;
    return $final_url;
  }

  public function getSlugFromTid ($tid) {
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $slug = \Drupal::service('path_alias.manager')->getAliasByPath('/taxonomy/term/'. $tid, $langcode);
    return substr($slug, 1);
  }

  /**
   * Custom check current user
   */
  public function checkUser ($key = null) {
    $res = [];
    $uid = \Drupal::currentUser()->id();
    // $uid = 0;
    // if (isset($_SESSION['auth0_current_uid']) && $_SESSION['auth0_current_uid'] != NULL && $_SESSION['auth0_current_uid'] != undefined) {
    //   $uid = $_SESSION['auth0_current_uid'];
    // }
    $user = \Drupal\user\Entity\User::load($uid);
    if ($uid > 0) {
      $name = $user->getUserName();
      if (isset($user->get('field_full_name')->getValue()[0])) {
        $name = $user->get('field_full_name')->getValue()[0]['value'];
      }
      $roles = $user->getRoles();
      $auth0 = 'false';
      if (in_array('auth0', $roles)) {
        $auth0 = 'true';
      }
      $res = [
        'name' => $name,
        'auth0' => $auth0,
      ];

      if ($key == 'class') {
        if ($uid > 0) {
          $res = 'login';
        } else {
          $res = 'n-login';
        }
      } else if ($key == 'auth0') {
        $res = $auth0;
      }
    }

    return $res;
  }

  /**
   * Custom url('<front>') function
   */
  public function urlFront ($type = null) {
    $url = base_path();
    if ($type == 'img') {
      return $url;
    }
    $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    return $url . $prefixes[$language] . '/';
  }

  /**
   * Custom url('<front>') function
   */
  public function urlFront2 ($type = null) {
    $url = base_path();
    if ($type == 'img') {
      return $url;
    }
    
    if ($type != null && $type != 'default') {
      return $type;
    }

    $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    return $url . $prefixes[$language] . '/';
  }

  public function urlImg ($targetId = null, $img_style = null) {
    if ($targetId != null) {
      $file = File::load($targetId);
      if ($img_style != null) {
        $uri = $file->getFileUri();
        $url = ImageStyle::load($img_style)->buildUrl($uri);
      } else {
        $url = $file->url();
      }
      return $url;
    }
  }

  public function gcs ($targetId = null) {
    if ($targetId != null) {
      $file = File::load($targetId);
      $name = $file->getFilename();
      $settings = \Drupal::config('gcs.settings');
      $url = $settings->get('gcs_root_folder') . $name;
      return $url;
    }
  }

  public function getRoute() {
    return \Drupal::routeMatch()->getRouteName();
  }

  public function allowedValueLabel($entity_type, $entity_name, $field_name) {
    $entityManager = \Drupal::service('entity_field.manager');
    $fields = $entityManager->getFieldStorageDefinitions($entity_type, $entity_name);
    $options = options_allowed_values($fields[$field_name]);
    
    return $options;
  }

  public function currentPath() {
    return \Drupal::service('path.current')->getPath();
  }

  public function blockValues ($block_machine_name) {
    // Load block
    $block =\Drupal\block\Entity\Block::load($block_machine_name);
    $uuid = $block->getPlugin()->getDerivativeId();
    $block_content =\Drupal::service('entity.repository')->loadEntityByUuid('block_content', $uuid);

    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if ($block_content->hasTranslation($language) == TRUE) {
      $block_content = $block_content->getTranslation($language);
    }
    return $block_content;
  }

  public function isNull ($type_value, $field_value) {
    if ($field_value == null) {
      return true;
    }
    else {
      if ($field_value[0][$type_value] == null) {
        return true;
      }
      else {
        return false;
      }
    }
  }

  public function valueLinkToNode ($value) {
    if (strpos($value, 'entity:node/') !== FALSE) {
      $nid = explode("/", $value);
      $nid = $nid[1];
      $url = base_path();
      $prefixes = \Drupal::config('language.negotiation')->get('url.prefixes');
      $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
      $url_alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/'. $nid, $langcode);
      $final_url = $url . $prefixes[$langcode] . $url_alias;
      return $final_url;
    }
    else {
      return $value;
    }
  }

  public function getTidPage () {
    if (\Drupal::routeMatch()->getRouteName() == 'entity.taxonomy_term.canonical') {
      $term = \Drupal::routeMatch()->getParameter('taxonomy_term');
      return $term->id();
    }
  }

  public function getLangCode () {
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    return $language;
  }

  public function getCaptchaSiteKey () {
    $moduleHandler = \Drupal::service('module_handler');
    $result = [];
    if ($moduleHandler->moduleExists('recaptcha_v3')) {
      $settings = \Drupal::config('recaptcha_v3.settings');
      $result['secret_k'] = $settings->get('secret_key') != null ? $settings->get('secret_key') : '6LdtiCIaAAAAADW4gSf9JDIAh4xgxyXA0PWRzfTX';
      $result['site_k'] = $settings->get('site_key') != null ? $settings->get('site_key') : '6LdtiCIaAAAAAJRPNRVVTQ-xjk-u3JjriVvWGJVr';
    }
    return $result;
  }

  public function siteSlogan () {
    $config = \Drupal::config('system.site');
    $site_slogan = $config->get('slogan');
    return $site_slogan;
  }

  public function entityTrans ($e) {
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if ($e->hasTranslation($language) == TRUE) {
      $e = $e->getTranslation($language);
    }
    return $e;
  }

  public function totalViewResult ($view_id, $view_display) {
    $view = views_get_view_result($view_id, $view_display);
    return count($view);
  }

  public function viewResult ($type, $view_id, $view_display, $contextual) {
    if ($type == 0) {
      $view = views_get_view_result($view_id, $view_display);
    } else if ($type == 1) {
      $view = views_get_view_result($view_id, $view_display, $contextual[0]);
    } else if ($type == 2) {
      $view = views_get_view_result($view_id, $view_display, $contextual[0], $contextual[1]);
    } else if ($type == 3) {
      $view = views_get_view_result($view_id, $view_display, $contextual[0], $contextual[1], $contextual[2]);
    }
    return $view;
  }

  public function term ($tid) {
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $term = \Drupal\taxonomy\Entity\Term::load($tid);
    if ($term->hasTranslation($language) == TRUE) {
      $term = $term->getTranslation($language);
    }
    return $term;
  }

  public function pLoad($pid) {
    $p = \Drupal\paragraphs\Entity\Paragraph::load($pid);
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    if ($p->hasTranslation($language) == TRUE) {
      $p = $p->getTranslation($language);
    }
    return $p;
  }

  public function errorPage() {
    $route_name = \Drupal::request()->attributes->get('_route');
    $class = $route_name;
    if (strpos($route_name, '404_error') !== false) {
      $class = 'error';
    }
    return $class;
  }

  public function authorizeUrl() {
    global $base_url;
    $settings = \Drupal::config('auth0.settings');
    $client_id = $settings->get('auth0_client_id');
    $client_secret = $settings->get('auth0_client_secret');
    $domain = $settings->get('auth0_domain');
    // $redirect_uri = $base_url . '/auth0_custom_callback/callback';
    $redirect_uri = $base_url . '/test/callback';

    $auth0_api = new Authentication(
      $domain,
      $client_id
    );

    $authorize_url = $auth0_api->get_authorize_link(
      // Response requested by the application.
      'code',
      // 'web_message',
      // Callback URL to respond to.
      $redirect_uri,
      // Connection to use, null for all.
      null,
      // State value to send with the request.
      $state_value,
      [
        // Optional API Audience to get an access token.
        'audience' => 'https://' . $domain . '/api/v2/',
        // Adjust ID token scopes requested.
        'scope' => 'openid email address',
        'prompt' => 'none',
      ]
    );

    return $authorize_url;
  }

  /**
   * Emits a breakpoint to the debug client.
   *
   * @param \Twig_Environment $environment
   *   The Twig environment instance.
   * @param array $context
   *   Variables from the Twig template.
   */
  public function drupalBreakpoint(\Twig_Environment $environment, array $context) {
    if (function_exists('xdebug_break')) {
      xdebug_break();
    }
    else {
      trigger_error('Could not make a break because xdebug is not available.', E_USER_WARNING);
    }
  }

  /**
   * Replaces all tokens in a given string with appropriate values.
   *
   * @param string $text
   *   An HTML string containing replaceable tokens.
   *
   * @return string
   *   The entered HTML text with tokens replaced.
   */
  public function tokenReplaceFilter($text) {
    return \Drupal::token()->replace($text);
  }

  /**
   * Performs a regular expression search and replace.
   *
   * @param string $text
   *   The text to search and replace.
   * @param string $pattern
   *   The pattern to search for.
   * @param string $replacement
   *   The string to replace.
   *
   * @return string
   *   The new text if matches are found, otherwise unchanged text.
   */
  public function pregReplaceFilter($text, $pattern, $replacement) {
    return preg_replace($pattern, $replacement, $text);
  }

  /**
   * Returns the URL of this image derivative for an original image path or URI.
   *
   * @param string $path
   *   The path or URI to the original image.
   * @param string $style
   *   The image style.
   *
   * @return string|null
   *   The absolute URL where a style image can be downloaded, suitable for use
   *   in an <img> tag. Requesting the URL will cause the image to be created.
   */
  public function imageStyle($path, $style) {

    if (!$image_style = ImageStyle::load($style)) {
      // trigger_error(sprintf('Could not load image style %s.', $style));
      return;
    }

    if (!$image_style->supportsUri($path)) {
      // trigger_error(sprintf('Could not apply image style %s.', $style));
      return;
    }

    return file_url_transform_relative($image_style->buildUrl($path));
  }

  /**
   * Transliterates text from Unicode to US-ASCII.
   *
   * @param string $string
   *   The string to transliterate.
   * @param string $langcode
   *   (optional) The language code of the language the string is in. Defaults
   *   to 'en' if not provided. Warning: this can be unfiltered user input.
   * @param string $unknown_character
   *   (optional) The character to substitute for characters in $string without
   *   transliterated equivalents. Defaults to '?'.
   * @param int $max_length
   *   (optional) If provided, return at most this many characters, ensuring
   *   that the transliteration does not split in the middle of an input
   *   character's transliteration.
   *
   * @return string
   *   $string with non-US-ASCII characters transliterated to US-ASCII
   *   characters, and unknown characters replaced with $unknown_character.
   */
  public function transliterate($string, $langcode = 'en', $unknown_character = '?', $max_length = NULL) {
    return \Drupal::transliteration()->transliterate($string, $langcode, $unknown_character, $max_length);
  }

  /**
   * Runs all the enabled filters on a piece of text.
   *
   * @param string $text
   *   The text to be filtered.
   * @param string|null $format_id
   *   (optional) The machine name of the filter format to be used to filter the
   *   text. Defaults to the fallback format. See filter_fallback_format().
   * @param string $langcode
   *   (optional) The language code of the text to be filtered.
   * @param array $filter_types_to_skip
   *   (optional) An array of filter types to skip, or an empty array (default)
   *   to skip no filter types.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The filtered text.
   *
   * @see check_markup()
   */
  public function checkMarkup($text, $format_id = NULL, $langcode = '', array $filter_types_to_skip = []) {
    return check_markup($text, $format_id, $langcode, $filter_types_to_skip);
  }

  /**
   * Truncates a UTF-8-encoded string safely to a number of characters.
   *
   * @param string $string
   *   The string to truncate.
   * @param int $max_length
   *   An upper limit on the returned string length, including trailing ellipsis
   *   if $add_ellipsis is TRUE.
   * @param bool $wordsafe
   *   (optional) If TRUE, attempt to truncate on a word boundary.
   * @param bool $add_ellipsis
   *   (optional) If TRUE, add '...' to the end of the truncated string.
   * @param int $min_wordsafe_length
   *   (optional) If TRUE, the minimum acceptable length for truncation.
   *
   * @return string
   *   The truncated string.
   *
   * @see \Drupal\Component\Utility\Unicode::truncate()
   */
  public function truncate($string, $max_length, $wordsafe = FALSE, $add_ellipsis = FALSE, $min_wordsafe_length = 1) {
    return Unicode::truncate($string, $max_length, $wordsafe, $add_ellipsis, $min_wordsafe_length);
  }

  /**
   * Adds new element to the array.
   *
   * @param array $build
   *   The renderable array to add the child item.
   * @param mixed $key
   *   The key of the new element.
   * @param mixed $element
   *   The element to add.
   *
   * @return array
   *   The modified array.
   */
  public function with(array $build, $key, $element) {
    if (is_array($key)) {
      NestedArray::setValue($build, $key, $element);
    }
    else {
      $build[$key] = $element;
    }
    return $build;
  }

  /**
   * Returns a render array for entity, field list or field item.
   *
   * @param mixed $object
   *   The object to build a render array from.
   * @param string|array $display_options
   *   Can be either the name of a view mode, or an array of display settings.
   * @param string $langcode
   *   (optional) For which language the entity should be rendered, defaults to
   *   the current content language.
   * @param bool $check_access
   *   (optional) Indicates that access check for an entity is required.
   *
   * @return array
   *   A render array to represent the object.
   */
  public function view($object, $display_options = 'default', $langcode = NULL, $check_access = TRUE) {
    if ($object instanceof FieldItemListInterface || $object instanceof FieldItemInterface) {
      return $object->view($display_options);
    }
    elseif ($object instanceof EntityInterface) {
      $access = $check_access ? $object->access('view', NULL, TRUE) : AccessResult::allowed();
      if ($access->isAllowed()) {
        $build = \Drupal::entityTypeManager()
          ->getViewBuilder($object->getEntityTypeId())
          ->view($object, $display_options, $langcode);
        CacheableMetadata::createFromRenderArray($build)
          ->merge(CacheableMetadata::createFromObject($object))
          ->merge(CacheableMetadata::createFromObject($access))
          ->applyTo($build);
        return $build;
      }
    }
  }

  /**
   * Filters out the children of a render array, optionally sorted by weight.
   *
   * @param array $build
   *   The render array whose children are to be filtered.
   * @param bool $sort
   *   Boolean to indicate whether the children should be sorted by weight.
   *
   * @return array
   *   The element's children.
   */
  public function children(array $build, $sort = FALSE) {
    $keys = Element::children($build, $sort);
    return array_intersect_key($build, array_flip($keys));
  }

  /**
   * Returns a URI to the file.
   *
   * @param object $input
   *   An object that contains the URI.
   *
   * @return string|null
   *   A URI that may be used to access the file.
   */
  public function fileUri($input) {
    if ($input instanceof EntityReferenceFieldItemListInterface) {
      $referenced_entities = $input->referencedEntities();
      if (isset($referenced_entities[0])) {
        return self::getUriFromEntity($referenced_entities[0]);
      }
    }
    elseif ($input instanceof EntityReferenceItem) {
      return self::getUriFromEntity($input->entity);
    }
    elseif ($input instanceof EntityInterface) {
      return self::getUriFromEntity($input);
    }
  }

  /**
   * Extracts file URI from content entity.
   *
   * @param object $entity
   *   Entity object that contains information about the file.
   *
   * @return string|null
   *   A URI that may be used to access the file.
   */
  private static function getUriFromEntity($entity) {
    if ($entity instanceof MediaInterface) {
      $source = $entity->getSource();
      $value = $source->getSourceFieldValue($entity);
      if ($source instanceof OEmbedInterface) {
        return $value;
      }
      elseif ($file = File::load($value)) {
        return $file->getFileUri();
      }
    }
    elseif ($entity instanceof FileInterface) {
      return $entity->getFileUri();
    }
  }

  /**
   * Returns a URL path to the file.
   *
   * @param string|object $input
   *   Can be either file URI or an object that contains the URI.
   *
   * @return string|null
   *   A URL that may be used to access the file.
   */
  public function fileUrl($input) {
    if (is_string($input)) {
      return file_url_transform_relative(file_create_url($input));
    }
    if ($input instanceof EntityReferenceFieldItemListInterface) {
      $referenced_entities = $input->referencedEntities();
      if (isset($referenced_entities[0])) {
        return self::getUrlFromEntity($referenced_entities[0]);
      }
    }
    elseif ($input instanceof EntityReferenceItem) {
      return self::getUrlFromEntity($input->entity);
    }
  }

  /**
   * Extracts file URL from content entity.
   *
   * @param object $entity
   *   Entity object that contains information about the file.
   *
   * @return string|null
   *   A URL that may be used to access the file.
   */
  private static function getUrlFromEntity($entity) {
    if ($entity instanceof MediaInterface) {
      $source = $entity->getSource();
      $value = $source->getSourceFieldValue($entity);
      if ($source instanceof OEmbedInterface) {
        return $value;
      }
      elseif ($file = File::load($value)) {
        return $file->createFileUrl();
      }
    }
    elseif ($entity instanceof FileInterface) {
      return $entity->createFileUrl();
    }
  }

  /**
   * Evaluates a string of PHP code.
   *
   * @param array $context
   *   Twig context.
   * @param string $code
   *   Valid PHP code to be evaluated.
   *
   * @return mixed
   *   The eval() result.
   */
  public function phpFilter(array $context, $code) {
    // Make Twig variables available in PHP code.
    extract($context);
    ob_start();
    // phpcs:ignore Drupal.Functions.DiscouragedFunctions.Discouraged
    print eval($code);
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
  }

}