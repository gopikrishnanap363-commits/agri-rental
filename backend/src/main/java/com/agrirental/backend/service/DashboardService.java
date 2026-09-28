package com.agrirental.backend.service;

import com.agrirental.backend.repository.BookingRepository;
import com.agrirental.backend.repository.EquipmentRepository;
import com.agrirental.backend.repository.UserRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;

import java.util.HashMap;
import java.util.Map;

@Service
@RequiredArgsConstructor
public class DashboardService {
    
    private final UserRepository userRepository;
    private final EquipmentRepository equipmentRepository;
    private final BookingRepository bookingRepository;
    
    public Map<String, Object> getDashboardStats() {
        Map<String, Object> stats = new HashMap<>();
        stats.put("totalUsers", userRepository.count());
        stats.put("totalEquipment", equipmentRepository.count());
        stats.put("totalBookings", bookingRepository.count());
        return stats;
    }
}
