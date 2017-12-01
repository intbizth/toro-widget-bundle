<?php

namespace Toro\Bundle\WidgetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Toro\Bundle\WidgetBundle\Twig\WidgetInterface;

class DefaultController extends Controller
{
    public function renderAction(Request $request)
    {
        $twig = $this->get('twig');
        $widget = $request->get('widget', []);
        $widgetName = null;

        if (!$notFound = empty($widget['name'])) {
            $widgetName = $this->get('toro.widget.registry')->getWidgetClass($widget['name']);
            $notFound = !$twig->hasExtension($widgetName);
        }

        if ($notFound) {
            // show empty response?
            throw new NotFoundHttpException(sprintf("Not found widget: %s", $widgetName));
        }

        /** @var WidgetInterface $widgetExtension */
        $widgetExtension = $twig->getExtension($widgetName);

        if (!$widgetExtension instanceof WidgetInterface) {
            // again! show an empty?
            throw new NotFoundHttpException(sprintf("Invalid widget type: %s", $widgetName));
        }

        $options = isset($widget['options']) ? $widget['options'] : [];
        $options['visibility'] = 'away';

        // convert data type
        array_walk_recursive($options, function (&$value) {
            if (in_array(strtolower($value), ['true', 'false'])) {
                $value = strtolower($value) === 'true' ? true : false;
            }

            if (is_numeric($value)) {
                $value = (int) $value;
            }
        });

        return new Response($widgetExtension->render($twig, $options));
    }
}
