<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 9/5/14
 * Time: 1:50 PM
 */

namespace Drupal\circles;


use Drupal\Core\Session\AccountInterface;
use Drupal\flag\FlagService;

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
    return $this->flagService->getFlagById('circle');
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

    $this->flagService->flagByObject($this->getFlag(), $circled, $circler);
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
      $this->flagService->unflagByObject($this->getFlag(), $circled, $circler);
    }
  }

}
