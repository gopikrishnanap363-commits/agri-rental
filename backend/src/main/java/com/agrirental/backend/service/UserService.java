package com.agrirental.backend.service;

import com.agrirental.backend.entity.FarmerProfile;
import com.agrirental.backend.entity.OwnerProfile;
import com.agrirental.backend.entity.User;
import com.agrirental.backend.entity.enums.UserRole;
import com.agrirental.backend.repository.FarmerProfileRepository;
import com.agrirental.backend.repository.OwnerProfileRepository;
import com.agrirental.backend.repository.UserRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

@Service
@RequiredArgsConstructor
public class UserService {
    
    private final UserRepository userRepository;
    private final FarmerProfileRepository farmerProfileRepository;
    private final OwnerProfileRepository ownerProfileRepository;

    public User getUserProfile(String mobile) {
        return userRepository.findByMobile(mobile)
            .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "User not found"));
    }

    public User updateProfile(String mobile, User updatedData) {
        User user = getUserProfile(mobile);
        if (updatedData.getName() != null) user.setName(updatedData.getName());
        if (updatedData.getAddress() != null) user.setAddress(updatedData.getAddress());
        if (updatedData.getCity() != null) user.setCity(updatedData.getCity());
        if (updatedData.getState() != null) user.setState(updatedData.getState());
        
        return userRepository.save(user);
    }
}
