<?php
// File: points_system.php

class PointsSystem {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function addPoints($user_id, $points, $reason) {
        $stmt = $this->conn->prepare("INSERT INTO points (user_id, points, reason) VALUES (?, ?, ?)");
        $stmt->bind_param("ids", $user_id, $points, $reason);
        return $stmt->execute();
    }

    public function getUserPoints($user_id) {
        $stmt = $this->conn->prepare("SELECT SUM(points) as total_points FROM points WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total_points'] ?? 0;
    }

    public function deductPoints($user_id, $points, $reason) {
        return $this->addPoints($user_id, -$points, $reason);
    }

    public function calculateReferralPoints($user_id) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as referral_count 
            FROM referrals 
            WHERE referrer_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $referral_count = $result['referral_count'];

        $points = $referral_count * 0.5; // 0.5 points per referral
        $this->addPoints($user_id, $points, "Yearly referral bonus");
        return $points;
    }

    public function calculateSubscriptionPoints($user_id) {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as subscription_count 
            FROM subscriptions 
            WHERE user_id IN (SELECT referred_id FROM referrals WHERE referrer_id = ?)
            AND status = 'active' AND created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $subscription_count = $result['subscription_count'];

        $points = $subscription_count * 0.5; // 0.5 points per subscription
        $this->addPoints($user_id, $points, "Yearly subscription bonus");
        return $points;
    }

    public function usePointsForYearlyMaintenance($user_id) {
        $required_points = 20; // 20 points for yearly maintenance
        $user_points = $this->getUserPoints($user_id);

        if ($user_points >= $required_points) {
            $this->deductPoints($user_id, $required_points, "Yearly maintenance fee");
            return true;
        }

        return false;
    }

    public function convertPointsToFriches($user_id, $points) {
        $friches = $points * 4; // 1 point = 4 Friches
        $user_points = $this->getUserPoints($user_id);

        if ($user_points >= $points) {
            $this->deductPoints($user_id, $points, "Converted to Friches");
            
            $stmt = $this->conn->prepare("UPDATE users SET friches = friches + ? WHERE id = ?");
            $stmt->bind_param("di", $friches, $user_id);
            if ($stmt->execute()) {
                $stmt = $this->conn->prepare("INSERT INTO friches_transactions (user_id, amount, transaction_type, description) VALUES (?, ?, 'earn', 'Converted from points')");
                $stmt->bind_param("id", $user_id, $friches);
                $stmt->execute();
                return true;
            }
        }

        return false;
    }
}