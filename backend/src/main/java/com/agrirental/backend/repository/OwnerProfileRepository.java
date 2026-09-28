package com.agrirental.backend.repository;

import com.agrirental.backend.entity.OwnerProfile;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;

@Repository
public interface OwnerProfileRepository extends JpaRepository<OwnerProfile, Long> {
}
