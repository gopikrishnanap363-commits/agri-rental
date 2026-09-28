package com.agrirental.backend.controller;

import com.agrirental.backend.entity.Booking;
import com.agrirental.backend.service.BookingService;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.security.core.Authentication;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/bookings")
@RequiredArgsConstructor
public class BookingController {
    
    private final BookingService bookingService;

    @PostMapping("/equipment/{equipmentId}")
    public ResponseEntity<Booking> createBooking(
            Authentication authentication, 
            @PathVariable Long equipmentId,
            @RequestBody Booking booking) {
        return ResponseEntity.ok(bookingService.createBooking(authentication.getName(), booking, equipmentId));
    }

    @GetMapping("/my-bookings")
    public ResponseEntity<List<Booking>> getMyBookings(Authentication authentication) {
        return ResponseEntity.ok(bookingService.getMyBookings(authentication.getName()));
    }
}
