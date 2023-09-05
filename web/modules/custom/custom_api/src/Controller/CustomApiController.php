<?php

namespace Drupal\custom_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CustomApiController extends ControllerBase
{

  //function to display data
  public function displayData(Request $request)
  {
    $query = \Drupal::entityQuery('node')
      ->condition('type', 'news')
      ->condition('status', 1)
      ->accessCheck(FALSE);

// for sepcific tags.
    $specific_tags = $request->query->get('specific_tags');
    if (empty($specific_tags)) {

      return new JsonResponse(['message' => 'No news tags provided.'], 400);
    }

    $tag_ids = $this->getTagIdsByTagNames($specific_tags);
    if (empty($tag_ids)) {

      return new JsonResponse(['message' => 'No news for the provided tags was found.'], 404);
    }

    $query->condition('field_category', $tag_ids, 'IN');

    $nids = $query->execute();
    $nodes = \Drupal\node\Entity\Node::loadMultiple($nids);

    $data = [];
    foreach ($nodes as $node) {

      $published_date = \Drupal::service('date.formatter')->format($node->getCreatedTime(), 'custom', 'd-m-Y');

      $tags = [];
      $tag_entities = $node->get('field_category')->referencedEntities();
      foreach ($tag_entities as $tag_entity) {
        $tags[] = $tag_entity->getName();
      }

      $data[] = [
        'title' => $node->getTitle(),
        'body' => $node->get('field_body')->value,
        'image' => $node->get('field_image')->entity->getFileUri(),
        'view_count' => $node->get('field_viewcount')->value,
        'published_date' => $published_date,
        'tags' => $tags,
      ];
    }

    $response = new JsonResponse($data);
    return $response;
  }

  /**
   * Get taxonomy term IDs by tag names.
   */
  protected function getTagIdsByTagNames($tag_names)
  {
    $query = \Drupal::entityQuery('taxonomy_term')
      ->condition('name', $tag_names, 'IN')
      ->condition('vid', 'tags')
      ->accessCheck(FALSE);
    return $query->execute();
  }
}