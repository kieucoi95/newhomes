<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* themes/newhomes/templates/includes/header.html.twig */
class __TwigTemplate_6f5919187f04dd1d5c015066b5601d8bdfe87aa970cd1959c39689761e2186f6 extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $tags = [];
        $filters = ["escape" => 1];
        $functions = ["attach_library" => 1, "url" => 7, "drupal_menu" => 10];

        try {
            $this->sandbox->checkSecurity(
                [],
                ['escape'],
                ['attach_library', 'url', 'drupal_menu']
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->env->getExtension('Drupal\Core\Template\TwigExtension')->attachLibrary("newhomes/fontawesome"), "html", null, true);
        echo "
<header>
  ";
        // line 4
        echo "  <div class=\"header-fix\">
    <div class=\"container\">
      <div class=\"logo\">
        <a href=\"";
        // line 7
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar($this->env->getExtension('Drupal\Core\Template\TwigExtension')->getUrl("<front>"));
        echo "\"><img src=\"";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["logo"] ?? null)), "html", null, true);
        echo "\" alt=\"logo\" height=\"50\"></a>
      </div>
      <div class=\"main-menu d-none d-lg-block\">
        ";
        // line 10
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->env->getExtension('Drupal\twig_tweak\TwigExtension')->drupalMenu("main"), "html", null, true);
        echo "
      </div>
      <div class=\"btn-menu-mobile d-lg-none d-block\">
        <i class=\"fad fa-bars\"></i>
      </div>
    </div>
  </div>
  ";
        // line 18
        echo "  <div id=\"header\">
    <div class=\"container\">
      <div class=\"logo\">
        <a href=\"";
        // line 21
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar($this->env->getExtension('Drupal\Core\Template\TwigExtension')->getUrl("<front>"));
        echo "\"><img src=\"";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["logo"] ?? null)), "html", null, true);
        echo "\" alt=\"logo\" height=\"50\"></a>
      </div>
      <div class=\"main-menu d-none d-lg-block\">
        ";
        // line 24
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->env->getExtension('Drupal\twig_tweak\TwigExtension')->drupalMenu("main"), "html", null, true);
        echo "
      </div>
      <div class=\"btn-menu-mobile d-lg-none d-block\">
        <i class=\"fad fa-bars\"></i>
      </div>
    </div>
  </div>
  <div id=\"header-mobile\" class=\"d-lg-none d-block\">
    <div class=\"logo\">
      <a href=\"";
        // line 33
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar($this->env->getExtension('Drupal\Core\Template\TwigExtension')->getUrl("<front>"));
        echo "\"><img src=\"";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["logo"] ?? null)), "html", null, true);
        echo "\" alt=\"logo\" height=\"50\"></a>
    </div>
    <div class=\"main-menu-mobile\">
      ";
        // line 36
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->env->getExtension('Drupal\twig_tweak\TwigExtension')->drupalMenu("main"), "html", null, true);
        echo "
    </div>
  </div>
  <div class=\"bg-main-menu-mb d-lg-none d-block\"></div>
</header>";
    }

    public function getTemplateName()
    {
        return "themes/newhomes/templates/includes/header.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  116 => 36,  108 => 33,  96 => 24,  88 => 21,  83 => 18,  73 => 10,  65 => 7,  60 => 4,  55 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "themes/newhomes/templates/includes/header.html.twig", "C:\\xampp\\htdocs\\newhomes\\newhomes\\web\\themes\\newhomes\\templates\\includes\\header.html.twig");
    }
}
