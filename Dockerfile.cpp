# CodeJudge C++ Execution Environment
FROM gcc:latest

# Install additional tools
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    nodejs \
    npm \
    openjdk-11-jdk \
    && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /app

# Create temp directory
RUN mkdir -p /app/temp && chmod 755 /app/temp

# Set resource limits
RUN echo "* soft nproc 50" >> /etc/security/limits.conf \
    && echo "* hard nproc 100" >> /etc/security/limits.conf \
    && echo "* soft memlock 256000" >> /etc/security/limits.conf \
    && echo "* hard memlock 512000" >> /etc/security/limits.conf

# Create non-root user for running code
RUN useradd -m -s /bin/bash coderunner
USER coderunner

# Test C++ environment
RUN echo '#include<iostream>' > test.cpp \
    && echo 'int main(){std::cout<<"Docker C++ OK"<<std::endl;return 0;}' >> test.cpp \
    && g++ -o test test.cpp \
    && ./test \
    && rm test test.cpp

COPY . /app
EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
