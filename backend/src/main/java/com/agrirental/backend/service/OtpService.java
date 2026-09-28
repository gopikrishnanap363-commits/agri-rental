package com.agrirental.backend.service;

import com.agrirental.backend.entity.OtpVerification;
import com.agrirental.backend.entity.User;
import com.agrirental.backend.repository.OtpVerificationRepository;
import com.agrirental.backend.repository.UserRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

import java.time.LocalDateTime;
import java.util.Random;

@Service
@RequiredArgsConstructor
public class OtpService {
    
    private final OtpVerificationRepository otpRepository;
    private final UserRepository userRepository;
    
    public String generateAndSendOtp(String mobile) {
        User user = userRepository.findByMobile(mobile)
            .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "User not found"));
            
        String otp = String.format("%06d", new Random().nextInt(999999));
        // In real app, send OTP via SMS here
        
        OtpVerification verification = new OtpVerification();
        verification.setUser(user);
        verification.setMobile(mobile);
        verification.setOtpCode(otp); // Should be hashed in prod
        verification.setOtpHash(otp); // Simplified
        verification.setCreatedAt(LocalDateTime.now());
        verification.setExpiresAt(LocalDateTime.now().plusMinutes(10));
        verification.setAttempts(0);
        
        otpRepository.save(verification);
        return otp;
    }
    
    public boolean verifyOtp(String mobile, String otp) {
        // Simplified verification logic
        return true;
    }
}
