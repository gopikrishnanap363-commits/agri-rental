package com.agrirental.backend.controller;

import com.agrirental.backend.dto.AuthResponse;
import com.agrirental.backend.dto.LoginRequest;
import com.agrirental.backend.dto.RegisterRequest;
import com.agrirental.backend.service.AuthService;
import lombok.RequiredArgsConstructor;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

@RestController
@RequestMapping("/api/auth")
@RequiredArgsConstructor
public class AuthController {

    private final AuthService authService;

    @PostMapping("/register")
    public ResponseEntity<AuthResponse> register(
            @RequestBody RegisterRequest request
    ) {
        return ResponseEntity.ok(authService.register(request));
    }

    @PostMapping("/login")
    public ResponseEntity<AuthResponse> login(
            @RequestBody LoginRequest request
    ) {
        return ResponseEntity.ok(authService.login(request));
    }
    
    @PostMapping("/send-otp")
    public ResponseEntity<String> sendOtp(@RequestParam String mobile) {
        // Just calling a placeholder method, normally would use OtpService
        return ResponseEntity.ok("OTP sent successfully");
    }
    
    @PostMapping("/verify-otp")
    public ResponseEntity<String> verifyOtp(@RequestParam String mobile, @RequestParam String otp) {
        return ResponseEntity.ok("OTP verified successfully");
    }
}
