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

/* themes/newhomes/templates/page--front.html.twig */
class __TwigTemplate_4de83cfa914380d0c650d9c46f54552daa593076a10c289736537efcaf5fdf8b extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $tags = ["include" => 50];
        $filters = ["escape" => 48];
        $functions = ["attach_library" => 48];

        try {
            $this->sandbox->checkSecurity(
                ['include'],
                ['escape'],
                ['attach_library']
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
        // line 48
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->env->getExtension('Drupal\Core\Template\TwigExtension')->attachLibrary("newhomes/swiper"), "html", null, true);
        echo "
";
        // line 49
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->env->getExtension('Drupal\Core\Template\TwigExtension')->attachLibrary("newhomes/front_page"), "html", null, true);
        echo "
";
        // line 50
        $this->loadTemplate((($context["directory"] ?? null) . "/templates/includes/header.html.twig"), "themes/newhomes/templates/page--front.html.twig", 50)->display($context);
        // line 51
        echo "
";
        // line 52
        $this->loadTemplate((($context["directory"] ?? null) . "/templates/includes/footer.html.twig"), "themes/newhomes/templates/page--front.html.twig", 52)->display($context);
        // line 53
        echo "

";
    }

    public function getTemplateName()
    {
        return "themes/newhomes/templates/page--front.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  70 => 53,  68 => 52,  65 => 51,  63 => 50,  59 => 49,  55 => 48,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "themes/newhomes/templates/page--front.html.twig", "C:\\xampp\\htdocs\\newhomes\\newhomes\\web\\themes\\newhomes\\templates\\page--front.html.twig");
    }
}
