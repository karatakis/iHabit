<?php
namespace App\Logic;

use App\Helper\LogicException;

/**
 * Contains all the logic related to Habits
 * TODO: Seperate database repository
 */
class HabitLogic extends AbstractLogic {

    /**
     * Used to list user habits
     */
    public function list(string $user_uuid) {
        $query = $this->connection->createQueryBuilder();

        $query
        ->select('*')
        ->from('habits')
        ->where('user_uuid = :user_uuid')
        ->setParameter('user_uuid', $user_uuid);

        return $query->execute()->fetchAll();
    }

    /**
     * Used to create a user habit
     */
    public function create(array $params, string $user_uuid) {
        $query = $this->connection->createQueryBuilder();

        $timestamp = new \DateTime();

        $query
        ->insert('habits')
        ->values([
            'name' => ':name',
            // TODO counter_value must me greater that 1
            'counter_value' => ':counter_value',
            'counter' => 0,
            'completed_today' => 0,
            'created_at' => ':created_at',
            'updated_at' => ':updated_at',
            'user_uuid' => ':user_uuid'
        ])
        ->setParameter('name', $params['name'])
        ->setParameter('counter_value', $params['counter_value'])
        ->setParameter('created_at', $timestamp->format('Y-m-d'))
        ->setParameter('updated_at', $timestamp->format('Y-m-d'))
        ->setParameter('user_uuid', $user_uuid);

        $query->execute();

        $id = $this->connection->lastInsertId();

        return $this->read($id, $user_uuid);
    }

    /**
     * Used to read specified habit
     */
    public function read(int $id, string $user_uuid) {
        $query = $this->connection->createQueryBuilder();

        $query
        ->select('*')
        ->from('habits')
        ->where('id = :id')
        ->setParameter('id', $id);

        $result = $query->execute()->fetch();

        if (!$result) {
            throw new LogicException('Cannot read Habit #'. $id . "\nHabit not found.", 404);
        }

        if ($result['user_uuid'] != $user_uuid) {
            throw new LogicException('Cannot read Habit #'. $id . "\nHabit is not associated with your account.", 403);
        }

        return $result;
    }

    /**
     * Used to complete habits when counter == counter_value
     */
    public function complete(int $id, string $user_uuid) {

        $habit = $this->read($id, $user_uuid);
        $timestamp = new \DateTime();

        if ($habit['counter_value'] == $habit['counter']) {
            return ['message' => 'Habit already completed for today', 'code' => 1];
        }

        $query = $this->connection->createQueryBuilder();
        $query
        ->update('habits')
        ->where('id = :id AND user_uuid = :user_uuid')
        ->set('updated_at', ':updated_at')
        ->setParameter('id', $id)
        ->setParameter('user_uuid', $user_uuid)
        ->setParameter('updated_at', $timestamp->format('Y-m-d'));

        $habit['counter'] += 1;

        if ($habit['counter_value'] == $habit['counter']) {
            $query->set('completed_today', 1);
        }

        $query->set('counter', 'counter + 1');

        if(!$query->execute()) {
            throw new LogicException('Cannot complete Habit #'. $id . "\nSomething went wrong, please try again latter.", 500);
        }

        if ($habit['counter_value'] == $habit['counter']) {
            // habit completed for today
            return ['message' => 'Habit completed for today', 'code' => 0];
        } else {
            // habit not completed yet
            return ['message' => 'Habit not completed yet', 'code' => -1];
        }
    }

    /**
     * Used to decreate the counter of Habit, reversing the action of complete
     */
    public function reverse(int $id, string $user_uuid) {
        $habit = $this->read($id, $user_uuid);
        $timestamp = new \DateTime();

        if ($habit['counter'] == 0) {
            return ['message' => 'Habit counter already at zero', 'code' => -1];
        }

        $query = $this->connection->createQueryBuilder();
        $query
        ->update('habits')
        ->where('id = :id AND user_uuid = :user_uuid')
        ->set('updated_at', ':updated_at')
        ->setParameter('id', $id)
        ->setParameter('user_uuid', $user_uuid)
        ->setParameter('updated_at', $timestamp->format('Y-m-d'));

        $habit['counter'] -= 1;

        if ($habit['counter_value'] > $habit['counter']) {
            $query->set('completed_today', 0);
        }

        $query->set('counter', 'counter - 1');

        if(!$query->execute()) {
            throw new LogicException('Cannot complete Habit #'. $id . "\nSomething went wrong, please try again latter.", 500);
        }

        if ($habit['counter_value'] == $habit['counter'] - 1) {
            // habit state changed
            return ['message' => 'Habit state changed to not complete', 'code' => 0];
        } else {
            // habit counter decreased
            return ['message' => 'Habit counter decreased', 'code' => 1];
        }
    }

    /**
     * Used to update habit information
     */
    public function update(int $id, string $user_uuid, array $params) {
        $habit = $this->read($id, $user_uuid);

        $timestamp = new \DateTime();

        $query = $this->connection->createQueryBuilder();
        $query
        ->update('habits')
        ->where('id = :id AND user_uuid = :user_uuid')
        ->set('updated_at', ':updated_at')
        ->setParameter('id', $id)
        ->setParameter('user_uuid', $user_uuid)
        ->setParameter('updated_at', $timestamp->format('Y-m-d'));

        // TODO validate user input

        if (! empty($params['name'])) {
            $query->set('name', ':name');
            $query->setParameter('name', $params['name']);
        }

        if (! empty($params['counter_value'])) {
            $query->set('counter_value', ':counter_value');

            // Check if counter_value is greater that daily count and complete/incomplete habit
            if ($habit['counter_value'] >= $params['counter_value'] &&
                $params['counter_value'] <= $habit['counter']) {
                    $query->set('counter', ':counter');
                    $query->setParameter('counter', $params['counter_value']);
                    $query->set('completed_today', 1);
            } else if ($habit['counter_value'] < $params['counter_value'] &&
                $params['counter_value'] > $habit['counter']) {
                    $query->set('completed_today', 0);
            }

            $query->setParameter('counter_value', $params['counter_value']);
        }

        $result = $query->execute();

        if(!$result) {
            throw new LogicException('Cannot update Habit #'. $id . "\nYou have not applied any modifications to the Habit.", 400);
        }

        return $this->read($id, $user_uuid);
    }

    /**
     * Used to delete a habit
     */
    public function destroy(int $id, string $user_uuid) {
        $query = $this->connection->createQueryBuilder();
        $query
        ->delete('habits')
        ->where('id = :id AND user_uuid = :user_uuid')
        ->setParameter('id', $id)
        ->setParameter('user_uuid', $user_uuid);

        if(!$query->execute()) {
            throw new LogicException('Cannot delete Habit #'. $id . "\nHabit does not exist or you do not meet the required permission to delete it.", 400);
        }

        return true;
    }
}