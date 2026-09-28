package com.agrirental.backend.service;

import com.agrirental.backend.entity.Booking;
import com.agrirental.backend.entity.Equipment;
import com.agrirental.backend.entity.User;
import com.agrirental.backend.entity.enums.BookingStatus;
import com.agrirental.backend.repository.BookingRepository;
import com.agrirental.backend.repository.EquipmentRepository;
import com.agrirental.backend.repository.UserRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

import java.time.LocalDateTime;
import java.util.List;

@Service
@RequiredArgsConstructor
public class BookingService {
    
    private final BookingRepository bookingRepository;
    private final EquipmentRepository equipmentRepository;
    private final UserRepository userRepository;

    public Booking createBooking(String farmerMobile, Booking bookingReq, Long equipmentId) {
        User farmer = userRepository.findByMobile(farmerMobile)
            .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Farmer not found"));
            
        Equipment equipment = equipmentRepository.findById(equipmentId)
            .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Equipment not found"));
            
        Booking booking = new Booking();
        booking.setFarmer(farmer);
        booking.setEquipment(equipment);
        booking.setStartDate(bookingReq.getStartDate());
        booking.setEndDate(bookingReq.getEndDate());
        booking.setStatus(BookingStatus.PENDING);
        booking.setTotalPrice(bookingReq.getTotalPrice());
        booking.setCreatedAt(LocalDateTime.now());
        
        return bookingRepository.save(booking);
    }
    
    public List<Booking> getMyBookings(String mobile) {
        User user = userRepository.findByMobile(mobile)
            .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "User not found"));
            
        if (user.getRole().name().equals("FARMER")) {
            return bookingRepository.findByFarmer(user);
        } else {
            // For owners, we'd need a custom query to find bookings for their equipment
            // Returning empty for now as a simple placeholder
            return List.of();
        }
    }
}
