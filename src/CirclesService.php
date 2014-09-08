<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 9/5/14
 * Time: 1:50 PM
 */

namespace Drupal\circles;


use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\flag\FlagService;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;

class CirclesService {

  /**
   * The FlagService injected from the container.
   *
   * @var FlagService
   */
  protected $flagService;

  /**
   * The current user injected from the container.
   *
   * @var AccountInterface
   */
  protected $current_user;

  /**
   * Constructs the CirclesService.
   *
   * @param FlagService $flag_service
   *   The Flag Service.
   * @param AccountInterface $account
   *   The current user.
   */
  public function __construct(FlagService $flag_service, AccountInterface $account) {
    $this->flagService = $flag_service;
    $this->current_user = $account;
  }

  public function getFlag() {
    return $this->flagService->getFlagById('circles');
  }

  /**
   * Adds a user to a circle.
   *
   * @param AccountInterface $circled
   *   The user being added to a circle.
   * @param array $circles
   *   An array of circles to which to add the user.
   * @param AccountInterface $circler
   *   Optional. The user adding someone to their circle. If NULL, the current
   *   user will be assumed.
   */
  public function encircle(AccountInterface $circled, array $circles, AccountInterface $circler = NULL) {
    // @todo: Figure out how circles are specified.
    if ($circler == NULL) {
      $circler = $this->current_user;
    }

    $user = User::load($circled->id());
    $this->flagService->flagByObject($this->getFlag(), $user, $circler);
  }

  /**
   * Remove a user from one or more circles.
   *
   * @param AccountInterface $circled
   *   The user being removed.
   * @param array|null $circles
   *   Optional. An array of circles from which to remove the user. If NULL,
   *   The encircled user will be removed from all the circler's circles.
   * @param AccountInterface|null $circler
   *   Optional. The user removing someone from their circles. If NULL, the
   *   current user will be assumed.
   */
  public function decircle(AccountInterface $circled, array $circles = NULL, AccountInterface $circler = NULL) {
    if ($circler == NULL) {
      $circler = $this->current_user;
    }

    // If no circles are specified, unflag the user entirely.
    if (empty($circles)) {
      $user = User::load($circled->id());
      $this->flagService->unflagByObject($this->getFlag(), $user, $circler);
    }
  }

  /**
   * Test if one user is in any of another user's circles.
   *
   * @param AccountInterface $account
   *   The user to test if they are in the circler's circles.
   * @param AccountInterface $circler
   *   The owner of the circle. If NULL, the current user is assumed.
   *
   * @return bool
   *   TRUE if $account is in $circler's circles. FALSE otherwise.
   */
  public function inCircles(AccountInterface $account, AccountInterface $circler = NULL) {
    if ($circler == NULL) {
      $circler = $this->current_user;
    }

    $flag = $this->getFlag();
    $user = User::load($account->id());

    if (!$flag->isFlagged($user, $circler)) {
      return FALSE;
    }

    $circles = [];
    $flaggings = $this->flagService->getFlaggings($user, $flag, $circler);
    foreach ($flaggings as $flagging) {
      // @todo Get circles from taxonomy values.
    }

    // @todo Return array of circles, not just TRUE or FALSE.
    return TRUE;
  }

  /**
   * Test if an account can view content.
   *
   * @param AccountInterface $account
   *   The account attempting to view content.
   * @param NodeInterface $node
   *   The content the account is attempting to view.
   *
   * @return bool
   *   TRUE if the account can view the content, FALSE otherwise.
   */
  public function canView(AccountInterface $account, NodeInterface $node) {
    $author = $node->getOwner();

    // @todo Grab the field name from a UI selection.
    if ($node->hasField('field_circle_tags')) {
      $values = $node->getValue();
      $node_circles = $values['field_circle_tags'];
    }

    return $this->inCircles($account, $author);
  }

}
